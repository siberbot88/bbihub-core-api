<?php

namespace App\Services;

use Exception;

/**
 * Midtrans Payment Service
 */
class MidtransService
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Snap Token for membership purchase
     */
    public function createSnapToken(array $params): string
    {
        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            return $snapToken;
        } catch (Exception $e) {
            throw new Exception('Failed to create snap token: ' . $e->getMessage());
        }
    }

    /**
     * Create Snap Transaction (returns object with token and redirect_url)
     */
    public function createSnapTransaction(array $params): object
    {
        try {
            // Returns object { token: "...", redirect_url: "..." }
            return \Midtrans\Snap::createTransaction($params);
        } catch (Exception $e) {
            throw new Exception('Failed to create snap transaction: ' . $e->getMessage());
        }
    }

    /**
     * Get transaction status from Midtrans
     */
    public function getTransactionStatus(string $orderId): object|array
    {
        try {
            return \Midtrans\Transaction::status($orderId);
        } catch (Exception $e) {
            throw new Exception('Failed to get transaction status: ' . $e->getMessage());
        }
    }

    /**
     * Verify notification from Midtrans webhook
     */
    public function verifyNotification(): object
    {
        try {
            $notification = new \Midtrans\Notification();
            return $notification;
        } catch (Exception $e) {
            throw new Exception('Invalid notification: ' . $e->getMessage());
        }
    }
}
