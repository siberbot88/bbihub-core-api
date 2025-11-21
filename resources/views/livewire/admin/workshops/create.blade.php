<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">Tambah Bengkel</h1>
      <p class="text-sm text-gray-500">Isi data bengkel di bawah ini.</p>
    </div>
    <a href="{{ route('admin.workshops.index') }}"
       class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">
      Kembali
    </a>
  </div>

  <form wire:submit.prevent="save"
        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">

    {{-- Code --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Kode Bengkel *</label>
      <input type="text" wire:model.defer="code" placeholder="Mis: BKL-0001"
             class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
      @error('code') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- Nama + Deskripsi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bengkel *</label>
        <input type="text" wire:model.defer="name" placeholder="Nama bengkel"
               class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
        <input type="text" wire:model.defer="description" placeholder="Deskripsi singkat"
               class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
    </div>

    {{-- Alamat --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Alamat *</label>
      <textarea wire:model.defer="address" rows="2" placeholder="Alamat lengkap"
                class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100"></textarea>
      @error('address') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- Kontak --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon *</label>
        <input type="text" wire:model.defer="phone" placeholder="Nomor telepon"
               class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('phone') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
        <input type="email" wire:model.defer="email" placeholder="Email bengkel"
               class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
    </div>

    {{-- Foto --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
      <input type="file" wire:model="photo" accept="image/*"
             class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
      @error('photo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror>

      @if ($photo)
        <div class="mt-3">
          <p class="text-xs text-gray-500 mb-1">Preview:</p>
          <img src="{{ $photo->temporaryUrl() }}" class="h-24 w-24 rounded-lg object-cover border">
        </div>
      @endif
    </div>

    {{-- Lokasi --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kota *</label>
        <input type="text" wire:model.defer="city" class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('city') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi *</label>
        <input type="text" wire:model.defer="province" class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('province') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Negara *</label>
        <input type="text" wire:model.defer="country" class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('country') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
        <input type="text" wire:model.defer="postal_code" class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('postal_code') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
    </div>

    {{-- Koordinat & Maps --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
        <input type="text" wire:model.defer="latitude" class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('latitude') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
        <input type="text" wire:model.defer="longitude" class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('longitude') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">URL Maps</label>
        <input type="text" wire:model.defer="maps_url" placeholder="Link Google Maps"
               class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('maps_url') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
    </div>

    {{-- Jam Operasional --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Buka *</label>
        <input type="time" wire:model.defer="opening_time"
               class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('opening_time') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Tutup *</label>
        <input type="time" wire:model.defer="closing_time"
               class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('closing_time') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Hari Operasional *</label>
        <input type="text" wire:model.defer="operational_days"
               placeholder="Mis: Senin - Sabtu"
               class="w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-100">
        @error('operational_days') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
      </div>
    </div>

    {{-- Status Aktif --}}
    <div class="flex items-center gap-2">
      <input type="checkbox" id="is_active" wire:model="is_active"
             class="rounded border-gray-300 text-red-600 focus:ring-red-500">
      <label for="is_active" class="text-sm text-gray-700">Bengkel aktif</label>
      @error('is_active') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    {{-- Tombol --}}
    <div class="flex items-center justify-end gap-3 pt-4">
      <a href="{{ route('admin.workshops.index') }}"
         class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
        Batal
      </a>
      <button type="submit"
              class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 shadow">
        Simpan Bengkel
      </button>
    </div>
  </form>
</div>
