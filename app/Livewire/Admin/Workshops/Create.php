<?php

namespace App\Livewire\Admin\Workshops;

use App\Models\Workshop;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $code;
    public $name;
    public $description;
    public $address;
    public $phone;
    public $email;
    public $photo;
    public $city;
    public $province;
    public $country;
    public $postal_code;
    public $latitude;
    public $longitude;
    public $maps_url;
    public $opening_time;
    public $closing_time;
    public $operational_days = 'Senin - Minggu';
    public $is_active = true;

    public function save()
    {
        $this->validate([
            'code'           => 'required|string|max:50|unique:workshops,code',
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'address'        => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'email'          => 'required|email|max:255',
            'photo'          => 'nullable|image|max:2048',
            'city'           => 'required|string|max:100',
            'province'       => 'required|string|max:100',
            'country'        => 'required|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'maps_url'       => 'nullable|url|max:255',
            'opening_time'   => 'required|date_format:H:i',
            'closing_time'   => 'required|date_format:H:i|after:opening_time',
            'operational_days' => 'required|string|max:255',
            'is_active'      => 'boolean',
        ]);

        $photoPath = $this->photo
            ? $this->photo->store('workshops', 'public')
            : null;

        Workshop::create([
            'user_uuid'       => Auth::id(), // owner bengkel
            'code'            => $this->code,
            'name'            => $this->name,
            'description'     => $this->description,
            'address'         => $this->address,
            'phone'           => $this->phone,
            'email'           => $this->email,
            'photo'           => $photoPath,
            'city'            => $this->city,
            'province'        => $this->province,
            'country'         => $this->country,
            'postal_code'     => $this->postal_code,
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'maps_url'        => $this->maps_url,
            'opening_time'    => $this->opening_time,
            'closing_time'    => $this->closing_time,
            'operational_days'=> $this->operational_days,
            'is_active'       => $this->is_active,
        ]);

        session()->flash('success', 'Bengkel berhasil ditambahkan.');

        return redirect()->route('admin.workshops.index'); // sesuaikan route index-mu
    }

    public function render()
    {
        return view('livewire.admin.workshops.create')
            ->layout('layouts.app');
    }
}
