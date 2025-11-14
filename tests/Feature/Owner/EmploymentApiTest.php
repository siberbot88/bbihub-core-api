<?php

namespace Tests\Feature\Owner;

use App\Mail\StaffCredentialsMail;
use App\Models\Employment;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmploymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat role owner & staff yang diperlukan (guard sanctum)
        Role::firstOrCreate(['name' => 'owner',   'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'mechanic','guard_name' => 'sanctum']);
    }

    /** Helper bikin owner + workshop */
    protected function makeOwnerWithWorkshop(): array
    {
        /** @var User $owner */
        $owner = User::factory()->create();

        // Set guard_name di model User
        $owner->guard_name = 'sanctum';
        // Assign role 'owner' (guard 'sanctum')
        $owner->assignRole('owner');

        /** @var Workshop $workshop */
        $workshop = Workshop::factory()->create([
            'user_uuid' => $owner->id,
        ]);

        return [$owner, $workshop];
    }

    /** @test */
    public function owner_can_create_mechanic_and_email_is_sent()
    {
        Mail::fake();

        [$owner, $workshop] = $this->makeOwnerWithWorkshop();

        $payload = [
            'name'          => 'Teknisi Satu',
            'username'      => 'teknisi1',
            'email'         => 'admin@gmail.com',
            'role'          => 'admin', // Role 'admin' (ada di 'sanctum' guard)
            'workshop_uuid' => $workshop->id,
            'specialist'    => 'AC',
            'jobdesk'       => 'Service AC & tune up',
        ];

        $response = $this
            ->actingAs($owner, 'sanctum') // Login sebagai owner dgn guard 'sanctum'
            ->postJson('/api/v1/owners/employee', $payload);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.user.email', $payload['email'])
            ->assertJsonPath('data.workshop_uuid', $workshop->id)
            ->assertJsonPath('email_sent', true);

        // pastikan user & employment tercatat di DB
        $this->assertDatabaseHas('users', [
            'email'                => $payload['email'],
            'must_change_password' => 1,
        ]);

        $this->assertDatabaseHas('employments', [
            'workshop_uuid' => $workshop->id,
            'specialist'    => 'AC',
        ]);

        // --- PERBAIKAN DI SINI (Baris 85) ---
        // Ganti assertSent menjadi assertQueued
        Mail::assertQueued(StaffCredentialsMail::class, function ($mail) use ($payload) {
            return $mail->hasTo($payload['email']);
        });
        // --- AKHIR PERBAIKAN ---
    }

    /** @test */
    public function cannot_create_employee_for_other_owners_workshop()
    {
        Mail::fake();

        // owner A
        [$ownerA, $workshopA] = $this->makeOwnerWithWorkshop();
        // owner B
        [$ownerB, $workshopB] = $this->makeOwnerWithWorkshop();

        // owner A mencoba daftar staff untuk workshop B -> harus 422 (exists rule gagal)
        $payload = [
            'name'          => 'Staff Ilegal',
            'username'      => 'illegal',
            'email'         => 'widjanarko020@gmail.com',
            'role'          => 'mechanic',
            'workshop_uuid' => $workshopB->id,  // workshop milik owner B
        ];

        $response = $this
            ->actingAs($ownerA, 'sanctum') // Login sebagai owner A dgn guard 'sanctum'
            ->postJson('/api/v1/owners/employee', $payload);

        $response->assertStatus(422);

        // Di sini kita cek email TIDAK DIKIRIM/DIANTRIKAN
        Mail::assertNothingSent();
        Mail::assertNothingQueued(); // Lebih baik tambahkan ini juga
    }
}
