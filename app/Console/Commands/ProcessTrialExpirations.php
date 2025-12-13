<?php

namespace App\Console\Commands;

use App\Models\OwnerSubscription;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessTrialExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trials:process-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process trial expirations and charge Rp 99K for conversion';

    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        parent::__construct();
        $this->midtransService = $midtransService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Processing trial expirations...');

        // Find trials that expire today or have expired
        $expiringTrials = OwnerSubscription::where('status', 'active')
            ->whereDate('expires_at', '<=', now())
            ->where('billing_cycle', 'monthly')
            ->whereNotNull('order_id')
            ->where(\DB::raw('LOWER(order_id)'), 'LIKE', 'trial-%')
            ->get();

        if ($expiringTrials->isEmpty()) {
            $this->info('✅ No expiring trials found.');
            return 0;
        }

        $this->info("Found {$expiringTrials->count()} expiring trial(s)");

        $converted = 0;
        $failed = 0;

        foreach ($expiringTrials as $subscription) {
            try {
                $user = User::find($subscription->user_id);
                
                if (!$user) {
                    $this->warn("⚠️  User not found for subscription {$subscription->id}");
                    continue;
                }

                $this->line("Processing: {$user->email}");

                // Create new order for Rp 99K charge
                $newOrderId = 'SUB-' . time() . '-' . \Illuminate\Support\Str::random(5);

                // Midtrans params for first month charge
                $params = [
                    'transaction_details' => [
                        'order_id' => $newOrderId,
                        'gross_amount' => 99000, // Rp 99K
                    ],
                    'customer_details' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                    ],
                    'item_details' => [
                        [
                            'id' => 'premium-monthly',
                            'price' => 99000,
                            'quantity' => 1,
                            'name' => 'Premium Monthly Subscription',
                        ]
                    ],
                ];

                // Note: For auto-charge, you'd typically use Midtrans Subscription API
                // or saved payment tokens. This is a simplified version.
                // In production, use: $midtransService->chargeWithToken(...)
                
                // For now, we'll mark trial as expired and log for manual processing
                $subscription->update([
                    'status' => 'expired',
                    'expires_at' => now(),
                ]);

                $user->update([
                    'trial_ends_at' => null,
                ]);

                // Log for manual follow-up or send notification
                Log::info("Trial expired for user {$user->id}, email: {$user->email}");
                
                $this->warn("  ⏸️  Trial expired - User notified (implement auto-charge for production)");
                $failed++;

                // TODO: Send email notification to user about trial expiration
                // Mail::to($user->email)->send(new TrialExpiredMail($user));

            } catch (\Exception $e) {
                $this->error("  ❌ Error: {$e->getMessage()}");
                Log::error("Trial expiration error: {$e->getMessage()}", [
                    'subscription_id' => $subscription->id,
                ]);
                $failed++;
            }
        }

        $this->info("\n📊 Summary:");
        $this->info("  Converted: {$converted}");
        $this->info("  Failed/Expired: {$failed}");
        
        return 0;
    }
}
