<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkshopUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_workshop()
    {
        Storage::fake('public');

        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $workshop = Workshop::factory()->create(['user_uuid' => $user->id]);

        $this->actingAs($user);

        $response = $this->putJson("/api/v1/owners/workshops/{$workshop->id}", [
            'name' => 'Updated Workshop Name',
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'operational_days' => 'Senin-Sabtu',
            'information' => 'Updated information',
            'is_active' => false,
        ]);

        if ($response->status() !== 200) {
            $response->dump();
        }
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Workshop updated successfully',
                'data' => [
                    'name' => 'Updated Workshop Name',
                    'opening_time' => '09:00:00',
                    'closing_time' => '18:00:00',
                    'operational_days' => 'Senin-Sabtu', // Adjust based on cast
                    'description' => 'Updated information',
                    'is_active' => false,
                ]
            ]);

        $this->assertDatabaseHas('workshops', [
            'id' => $workshop->id,
            'name' => 'Updated Workshop Name',
            'description' => 'Updated information',
            'is_active' => 0,
        ]);
    }

    public function test_owner_can_update_workshop_photo()
    {
        Storage::fake('public');

        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $workshop = Workshop::factory()->create(['user_uuid' => $user->id]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('workshop.jpg');

        $response = $this->putJson("/api/v1/owners/workshops/{$workshop->id}", [
            'photo' => $file,
        ]);

        $response->assertStatus(200);

        // Check if file stored
        // Note: The controller stores it in 'workshops' folder
        // We need to verify the file exists. 
        // Since the filename is hashed, we can just check if any file exists in the directory or check the DB.
        
        $workshop->refresh();
        $this->assertStringContainsString('storage/workshops', $workshop->photo);
    }
}
