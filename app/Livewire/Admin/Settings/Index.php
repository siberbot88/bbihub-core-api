<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;

#[Title('Pengaturan')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    #[Url] public string $tab = 'general'; // general|branding|roles|notify|security|locale

    // --- Umum ---
    public string $app_name = 'BBI HUB Plus';
    public string $app_tagline = 'Platform bengkel modern';
    public string $contact_email = 'support@example.com';

    // --- Branding ---
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $logo_light;
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $logo_dark;
    public string $primary_color = '#2563eb';

    // --- Roles & izin (contoh sederhana) ---
    public array $roleMatrix = [
        'Admin'   => ['users'=>true,'workshops'=>true,'vehicles'=>true,'billing'=>true],
        'Owner'   => ['users'=>false,'workshops'=>true,'vehicles'=>true,'billing'=>true],
        'Mekanik' => ['users'=>false,'workshops'=>true,'vehicles'=>false,'billing'=>false],
    ];

    // --- Notifikasi ---
    public bool $notif_email = true;
    public bool $notif_push  = true;
    public bool $notif_whatsapp = false;

    // --- Keamanan ---
    public bool $force_2fa = false;
    public bool $password_expiry = false;
    public int  $password_days = 90;
    public bool $single_session = true;

    // --- Lokalitas ---
    public string $timezone = 'Asia/Jakarta';
    public string $locale   = 'id';
    public string $date_format = 'd M Y';

    // handlers (mock/save)
    public function saveGeneral(){ $this->dispatch('toast', body: 'Pengaturan umum disimpan.'); }
    public function saveBranding(){ $this->dispatch('toast', body: 'Branding disimpan.'); }
    public function saveRoles(){ $this->dispatch('toast', body: 'Role & izin disimpan.'); }
    public function saveNotify(){ $this->dispatch('toast', body: 'Preferensi notifikasi disimpan.'); }
    public function saveSecurity(){ $this->dispatch('toast', body: 'Kebijakan keamanan disimpan.'); }
    public function saveLocale(){ $this->dispatch('toast', body: 'Lokalitas disimpan.'); }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}
