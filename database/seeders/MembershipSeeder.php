<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first workshop (you can modify this to seed for all workshops)
        $workshop = Workshop::first();
        
        if (!$workshop) {
            $this->command->warn('No workshop found. Please create a workshop first.');
            return;
        }

        $memberships = [
            [
                'workshop_id' => $workshop->id,
                'name' => 'Bronze',
                'description' => 'Perfect for occasional customers. Get started with basic benefits and savings.',
                'discount_percentage' => 5.00,
                'points_multiplier' => 1.0,
                'price' => 50000.00,
                'duration_months' => 1,
                'is_active' => true,
                'benefits' => [
                    '5% discount on all services',
                    'Earn 1 point per Rp 1,000',
                    'Priority booking notification',
                ],
            ],
            [
                'workshop_id' => $workshop->id,
                'name' => 'Silver',
                'description' => 'Great value for regular customers. Save more with better benefits.',
                'discount_percentage' => 10.00,
                'points_multiplier' => 1.5,
                'price' => 150000.00,
                'duration_months' => 3,
                'is_active' => true,
                'benefits' => [
                    '10% discount on all services',
                    'Earn 1.5 points per Rp 1,000',
                    'Priority booking',
                    'Free vehicle wash monthly',
                ],
            ],
            [
                'workshop_id' => $workshop->id,
                'name' => 'Gold',
                'description' => 'Best value for frequent customers. Maximum savings and exclusive perks.',
                'discount_percentage' => 15.00,
                'points_multiplier' => 2.0,
                'price' => 500000.00,
                'duration_months' => 12,
                'is_active' => true,
                'benefits' => [
                    '15% discount on all services',
                    'Earn 2 points per Rp 1,000',
                    'Priority booking (top priority)',
                    'Free vehicle wash unlimited',
                    'Free pick-up & delivery service',
                    'Annual vehicle inspection included',
                ],
            ],
        ];

        foreach ($memberships as $membershipData) {
            Membership::updateOrCreate(
                [
                    'workshop_id' => $membershipData['workshop_id'],
                    'name' => $membershipData['name'],
                ],
                $membershipData
            );
        }

        $this->command->info('Membership tiers created successfully for workshop: ' . $workshop->name);
    }
}
