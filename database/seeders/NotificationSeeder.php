<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Try to find "Dian Sastro" first (from user screenshot)
        $user = User::where('name', 'like', '%Dian Sastro%')->first();

        // If not found, fallback to first user
        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            $this->command->info('No user found to attach notifications to.');
            return;
        }

        DB::table('notifications')->insert([
            [
                'id' => Str::uuid(),
                'user_uuid' => $user->id,
                'title' => 'Laporan Bulanan Siap',
                'message' => 'Laporan kinerja bengkel bulan November telah tersedia untuk diunduh.',
                'type' => 'report_ready',
                'data' => null,
                'is_read' => false,
                'created_at' => now()->subMinutes(5),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_uuid' => $user->id,
                'title' => 'Servis Baru Ditugaskan',
                'message' => 'Servis #SRV-2023001 membutuhkan penugasan mekanik segera.',
                'type' => 'task_assignment',
                'data' => json_encode(['service_id' => 'dummy-service-id-123', 'screen' => 'service_detail']),
                'is_read' => false,
                'created_at' => now()->subHours(1),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_uuid' => $user->id,
                'title' => 'Feedback Pelanggan',
                'message' => 'Pelanggan Budi memberikan bintang 5 untuk layanan Ganti Oli.',
                'type' => 'feedback_received',
                'data' => json_encode(['related_id' => 'dummy-feedback-id', 'screen' => 'feedback_detail']),
                'is_read' => true,
                'created_at' => now()->subDays(1),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_uuid' => $user->id,
                'title' => 'Sistem Update',
                'message' => 'BBI Hub telah diperbarui ke versi 1.2.0 dengan fitur baru.',
                'type' => 'system',
                'data' => null,
                'is_read' => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now(),
            ],
        ]);
        
        $this->command->info('Notifications seeded for user: ' . $user->email);
    }
}
