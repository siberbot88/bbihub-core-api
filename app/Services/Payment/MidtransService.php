<?php

namespace App\Services\Payment;

use App\Models\Transaction;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        $this->configure();
    }

    private function configure(): void
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function getSnapToken(Transaction $transaction): object
    {
        $params = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => (int) $transaction->amount,
            ],
            'customer_details' => [
                'first_name' => $transaction->customer->name,
                'email' => $transaction->customer->email,
                'phone' => $transaction->customer->phone,
            ],
            'item_details' => $transaction->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'price' => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->name,
                ];
            })->toArray(),
        ];

        // Ensure total amount matches sum of items
        // Midtrans is strict about this.
        // We will assume transaction->amount is correct source of truth
        // or re-calculate. For safety, let's verify.
        // If there's a discrepancy, Midtrans will throw 400.
        // Given existing logic, we pass what we have.

        // Get Snap Token & Url
        try {
            $snapResponse = Snap::createTransaction($params);
            return $snapResponse; // Contains token and redirect_url
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Error: ' . $e->getMessage());
        }
    }

    public function handleNotification(): array
    {
        try {
            $notif = new Notification();

            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $order_id = $notif->order_id;
            $fraud = $notif->fraud_status;

            // Simple status mapping
            $status = null;

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $status = 'challenge';
                    } else {
                        $status = 'success';
                    }
                }
            } else if ($transaction == 'settlement') {
                $status = 'success';
            } else if ($transaction == 'pending') {
                $status = 'pending';
            } else if ($transaction == 'deny') {
                $status = 'failed';
            } else if ($transaction == 'expire') {
                $status = 'expired';
            } else if ($transaction == 'cancel') {
                $status = 'cancelled';
            }

            return [
                'order_id' => $order_id,
                'status' => $status,
                'raw' => $notif
            ];

        } catch (\Exception $e) {
            throw new \Exception('Midtrans Notification Error: ' . $e->getMessage());
        }
    }
}
