<?php

namespace App\Livewire\Admin\Promotions;

use App\Models\Promotion;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Tambah Banner')]
#[Layout('layouts.app')]
class Create extends Component
{
    use WithFileUploads;

    // field form
    public string $title = '';
    public ?string $location = null;
    public ?string $link = null;
    public string $status = 'draft';
    public $image; // uploaded file

    // opsi lokasi banner
    public array $locationOptions = [
        'home_hero'   => 'Homepage - Banner Utama',
        'home_middle' => 'Homepage - Tengah',
        'product_top' => 'Halaman Produk - Atas',
    ];

    // opsi status
    public array $statusOptions = [
        'draft'  => 'Draft',
        'active' => 'Aktif',
    ];

    protected function rules(): array
    {
        return [
            'title'    => ['required', 'string', 'max:150'],
            'location' => ['required', 'string'],
            'link'     => ['nullable', 'url'],
            'status'   => ['required', 'string'],
            'image'    => ['required', 'image', 'max:5120'], // 5 MB
        ];
    }

    public function save()
    {
        $data = $this->validate();

        // simpan file ke storage/app/public/promotions/banners
        $path = $this->image->store('promotions/banners', 'public');

        // sesuaikan dengan kolom di tabel promotions kamu
        Promotion::create([
            'title'      => $data['title'],
            'location'   => $data['location'],
            'link'       => $data['link'],
            'status'     => $data['status'],
            'image_path' => $path,
            'type'       => 'banner', // kalau punya kolom type
        ]);

        session()->flash('success', 'Banner berhasil dibuat.');

        // route name-nya: admin.promotions.index
        return redirect()->route('admin.promotions.index');
    }

    public function render()
    {
        return view('livewire.admin.promotions.create', [
            'locationOptions' => $this->locationOptions,
            'statusOptions'   => $this->statusOptions,
        ]);
    }
}
