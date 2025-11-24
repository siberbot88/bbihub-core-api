<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Voucher;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VoucherApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workshop $workshop;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'owner', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'superadmin', 'guard_name' => 'sanctum']);

        $this->user = User::factory()->create();
        $this->user->assignRole('owner');
        
        $this->workshop = Workshop::factory()->create();
        Sanctum::actingAs($this->user);
        Storage::fake('public');
    }

    /** @test */
    public function it_can_get_a_list_of_vouchers()
    {
        Voucher::factory(3)->create(['workshop_uuid' => $this->workshop->id]);

        // PERUBAHAN: URL menggunakan endpoint 'owners'
        $response = $this->getJson('/api/v1/owners/vouchers');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_filter_active_vouchers()
    {
        Voucher::factory()->create([
            'workshop_uuid' => $this->workshop->id,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);
        Voucher::factory()->create([
            'workshop_uuid' => $this->workshop->id,
            'valid_until' => now()->subDay(),
        ]);

        // PERUBAHAN: URL menggunakan endpoint 'owners'
        $response = $this->getJson('/api/v1/owners/vouchers?status=active');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_create_a_voucher()
    {
        $data = [
            'workshop_uuid' => $this->workshop->id,
            'code_voucher' => 'TESTCODE',
            'title' => 'Voucher Test',
            'discount_value' => 10000,
            'quota' => 50,
            'min_transaction' => 50000,
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addMonth()->toDateString(),
            'is_active' => true,
            'image' => UploadedFile::fake()->image('voucher.jpg')
        ];

        // PERUBAHAN: URL menggunakan endpoint 'owners'
        $response = $this->postJson('/api/v1/owners/vouchers', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Voucher Test');

        $this->assertDatabaseHas('vouchers', ['code_voucher' => 'TESTCODE']);
        Storage::disk('public')->assertExists('vouchers/' . $data['image']->hashName());
    }

    /** @test */
    public function it_can_get_a_single_voucher()
    {
        $voucher = Voucher::factory()->create(['workshop_uuid' => $this->workshop->id]);

        // PERUBAHAN: URL menggunakan endpoint 'owners'
        $response = $this->getJson('/api/v1/owners/vouchers/' . $voucher->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $voucher->id);
    }

    /** @test */
    public function it_can_update_a_voucher()
    {
        $voucher = Voucher::factory()->create(['workshop_uuid' => $this->workshop->id]);

        $data = [
            'title' => 'Updated Title',
            'quota' => 99,
        ];

        // PERUBAHAN: URL menggunakan endpoint 'owners'
        $response = $this->patchJson('/api/v1/owners/vouchers/' . $voucher->id, $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'quota' => 99]);
    }

    /** @test */
    public function it_can_delete_a_voucher()
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('vouchers', 'public');

        $voucher = Voucher::factory()->create([
            'workshop_uuid' => $this->workshop->id,
            'image' => $path
        ]);

        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id]);
        Storage::disk('public')->assertExists($path);

        // PERUBAHAN: URL menggunakan endpoint 'owners'
        $response = $this->deleteJson('/api/v1/owners/vouchers/' . $voucher->id);

        $response->assertStatus(204); // No Content
        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
