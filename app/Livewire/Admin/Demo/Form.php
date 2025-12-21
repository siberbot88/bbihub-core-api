<?php

namespace App\Livewire\Admin\Demo;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Service;
use App\Models\Workshop;
use App\Models\Transaction;
use App\Models\TransactionItem; // Assuming logic might need it, generally Service -> Transaction link is direct
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Form extends Component
{
    // Wizard Step
    public int $step = 1;

    // --- STEP 1: CUSTOMER ---
    public $customerName;
    public $customerPhone;
    public $customerEmail;
    public $customerAddress;
    public ?Customer $customer = null;

    // --- STEP 2: VEHICLE ---
    public $vehiclePlate;
    public $vehicleBrand;
    public $vehicleModel;
    public $vehicleType = 'motor'; // default
    public $vehicleYear;
    public $vehicleColor;
    public ?Vehicle $vehicle = null;

    // --- STEP 3: SERVICE ---
    public $workshopId;
    public $serviceName; // Masalah/Keluhan utama
    public $serviceDesc;
    public $servicePrice = 0;
    public ?Service $service = null;

    // --- STEP 4: PAYMENT ---
    public ?Transaction $transaction = null;
    public $voucherCode = '';
    public $discountAmount = 0;
    public $finalAmount = 0;
    public $snapToken = null;
    public $voucherMessage = '';

    // --- STEP 5: FEEDBACK ---
    public $rating = 0;
    public $feedbackComment = '';

    // Options
    public $workshops = [];

    protected $rules = [
        1 => [
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|string|max:20',
            'customerEmail' => 'nullable|email',
            'customerAddress' => 'nullable|string',
        ],
        2 => [
            'vehiclePlate' => 'required|string|max:20',
            'vehicleBrand' => 'required|string',
            'vehicleModel' => 'required|string',
            'vehicleYear' => 'required|numeric|digits:4',
        ],
        3 => [
            'workshopId' => 'required|exists:workshops,id',
            'serviceName' => 'required|string', // Keluhan
            'servicePrice' => 'required|numeric|min:0',
        ],
        5 => [
            'rating' => 'required|integer|min:1|max:5',
            'feedbackComment' => 'nullable|string|max:1000',
        ]
    ];

    public function mount()
    {
        // Load workshops for dropdown
        // If superadmin, show all. If admin/owner, restrict.
        // For simplicity in demo, showing all workshops or based on auth
        if (auth()->user()->hasRole('superadmin')) {
            $this->workshops = Workshop::select('id', 'name')->get();
        } else {
            // Get user's workshops
            $this->workshops = auth()->user()->workshops ?? collect();
            if ($this->workshops->isEmpty() && auth()->user()->employment) {
                // If mechanic/admin
                $this->workshops = collect([auth()->user()->employment->workshop]);
            }
        }

        // Auto select if only 1 workshop
        if (count($this->workshops) === 1) {
            $this->workshopId = $this->workshops[0]->id;
        }
    }

    public function nextStep()
    {
        $this->validate($this->rules[$this->step] ?? []);

        if ($this->step === 1) {
            $this->saveCustomer();
            $this->step++;
        } elseif ($this->step === 2) {
            $this->saveVehicle();
            $this->step++;
        } elseif ($this->step === 3) {
            $this->saveServiceAndTransaction();
            // Finish flow here
            session()->flash('message', 'Servis berhasil dibuat! Menunggu pengerjaan bengkel.');
            $this->resetForm();
        }
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    // --- ACTIONS ---

    public function resetForm()
    {
        $this->reset([
            'step',
            'customer',
            'vehicle',
            'service',
            'transaction',
            'customerName',
            'customerPhone',
            'customerEmail',
            'customerAddress',
            'vehiclePlate',
            'vehicleBrand',
            'vehicleModel',
            'vehicleType',
            'vehicleYear',
            'vehicleColor',
            'serviceName',
            'serviceDesc',
            'servicePrice',
            'voucherCode',
            'discountAmount',
            'finalAmount',
            'snapToken',
            'voucherMessage',
            'rating',
            'feedbackComment'
        ]);

        $this->step = 1;
        $this->mount(); // Re-init workshop selection
    }

    // --- TABLE ACTIONS ---

    public function markAsComplete($serviceId)
    {
        $srv = Service::find($serviceId);
        if ($srv && $srv->status === 'pending') {
            $srv->update(['status' => 'completed']); // Ready for payment
            session()->flash('message', 'Status servis diperbarui: Selesai Dikerjakan.');
        }
    }

    public function openPayment($transactionId)
    {
        $trx = Transaction::with(['customer', 'vehicle', 'service'])->find($transactionId);

        if (!$trx)
            return;

        // Load data into component
        $this->transaction = $trx;
        $this->customer = $trx->customer;
        $this->vehicle = $trx->vehicle ?? $trx->service->vehicle; // Fallback
        $this->service = $trx->service;
        $this->workshopId = $trx->workshop_uuid;

        $this->finalAmount = $trx->amount;
        $this->discountAmount = 0;
        $this->voucherCode = '';
        $this->voucherMessage = '';
        $this->snapToken = null;

        // Jump to payment step (Visual only)
        $this->step = 4;
    }

    public function openFeedback($transactionId)
    {
        $trx = Transaction::with(['customer', 'vehicle', 'service'])->find($transactionId);

        if (!$trx)
            return;

        // Load data into component
        $this->transaction = $trx;
        $this->customer = $trx->customer;
        $this->vehicle = $trx->vehicle;
        $this->service = $trx->service;
        $this->workshopId = $trx->workshop_uuid;

        $this->rating = 0;
        $this->feedbackComment = '';

        // Jump to feedback step
        $this->step = 5;
    }

    protected function saveCustomer()
    {
        // Demo feature: Create new customer every time or find existing by phone? 
        // Request says "save at each step", implies persistence.
        // Let's create or update based on phone for demo convenience

        $this->customer = Customer::updateOrCreate(
            ['phone' => $this->customerPhone],
            [
                'name' => $this->customerName,
                'email' => $this->customerEmail,
                'address' => $this->customerAddress,
                'code' => 'CUST-' . Str::random(5), // Simple code gen
            ]
        );
    }

    protected function saveVehicle()
    {
        if (!$this->customer) {
            // Should not happen if flow is followed
            $this->addError('customer', 'Customer data missing.');
            return;
        }

        $this->vehicle = Vehicle::updateOrCreate(
            [
                'plate_number' => $this->vehiclePlate,
                'customer_uuid' => $this->customer->id
            ],
            [
                'brand' => $this->vehicleBrand,
                'model' => $this->vehicleModel,
                'year' => $this->vehicleYear,
                'type' => $this->vehicleType,
                'color' => $this->vehicleColor ?? '-',
                'name' => "$this->vehicleBrand $this->vehicleModel",
                'code' => 'VEH-' . strtoupper(Str::random(5)),
            ]
        );
    }

    protected function saveServiceAndTransaction()
    {
        if (!$this->customer || !$this->vehicle) {
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Create Service
            $this->service = Service::create([
                'workshop_uuid' => $this->workshopId,
                'customer_uuid' => $this->customer->id,
                'vehicle_uuid' => $this->vehicle->id,
                'name' => $this->serviceName, // Title/Complaint
                'description' => $this->serviceDesc ?? 'Demo Service Entry',
                'status' => 'pending', // Initial status
                'price' => $this->servicePrice,
                'scheduled_date' => now(),
                'code' => 'SRV-' . time(),
            ]);

            // 2. Create Transaction (Pending)
            $this->transaction = Transaction::create([
                'service_uuid' => $this->service->id,
                'customer_uuid' => $this->customer->id,
                'workshop_uuid' => $this->workshopId,
                'amount' => $this->servicePrice,
                'status' => 'pending', // unpaid
                // 'payment_method' => null, (set later)
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('system', 'Failed to save service: ' . $e->getMessage());
        }
    }

    // --- PAYMENT & VOUCHER ---

    public function checkVoucher()
    {
        $this->reset('discountAmount', 'finalAmount', 'voucherMessage');
        $this->finalAmount = $this->transaction->amount; // Reset base

        if (empty($this->voucherCode))
            return;

        $voucher = Voucher::where('code_voucher', $this->voucherCode)
            ->where('workshop_uuid', $this->workshopId)
            ->first();

        // Simple validation logic (can be expanded based on Voucher model logic)
        if (!$voucher) {
            $this->addError('voucherCode', 'Voucher tidak ditemukan.');
            return;
        }

        if ($voucher->status !== 'active') {
            $this->addError('voucherCode', 'Voucher tidak aktif atau kadaluarsa.');
            return;
        }

        if ($this->transaction->amount < $voucher->min_transaction) {
            $this->addError('voucherCode', "Minimal transaksi Rp " . number_format($voucher->min_transaction));
            return;
        }

        // Apply
        $this->discountAmount = $voucher->discount_value;
        $this->finalAmount = max(0, $this->transaction->amount - $this->discountAmount);
        $this->voucherMessage = "Voucher applied: -Rp " . number_format($this->discountAmount);
    }

    public function processPayment()
    {
        // 1. Update Transaction with final details
        $this->transaction->update([
            'amount' => $this->finalAmount,
        ]);

        // 2. Midtrans Snap
        // Config Midtrans
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key') ?? env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false) ?? env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $this->transaction->id . '-' . time(), // Unique ID for Midtrans
                'gross_amount' => (int) $this->finalAmount,
            ],
            'customer_details' => [
                'first_name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ],
            'item_details' => [
                [
                    'id' => $this->service->id,
                    'price' => (int) $this->finalAmount,
                    'quantity' => 1,
                    'name' => "Service: " . $this->service->name
                ]
            ]
        ];

        try {
            $this->snapToken = \Midtrans\Snap::getSnapToken($params);

            // Dispatch event for frontend to open Snap Popup
            $this->dispatch('snap-token-generated', ['token' => $this->snapToken]);

        } catch (\Exception $e) {
            $this->addError('payment', 'Midtrans Error: ' . $e->getMessage());
        }
    }

    // Called after success payment (frontend can redirect or calling this)
    public function finishDemo()
    {
        // Update status to paid/completed
        $this->transaction->update(['status' => 'paid']);
        $this->service->update(['status' => 'completed']);

        session()->flash('message', 'Pembayaran Berhasil! Silakan beri penilaian Anda.');

        // Go to feedback step
        $this->step = 5;
    }

    public function submitFeedback()
    {
        $this->validate($this->rules[5]);

        \App\Models\Feedback::create([
            'transaction_uuid' => $this->transaction->id,
            'rating' => $this->rating,
            'comment' => $this->feedbackComment,
            'submitted_at' => now(),
        ]);

        session()->flash('message', 'Terima kasih atas masukan Anda! Demo selesai.');
        $this->resetForm();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        // Fetch recent services/transactions for the list
        // Limit to 10 latest
        $recentServices = Service::with(['customer', 'vehicle', 'workshop', 'transaction.feedback']) // Eager load feedback
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.admin.demo.form', [
            'recentServices' => $recentServices
        ]);
    }
}
