<div class="min-h-screen">
  <div class="mx-auto max-w-7xl px-4 py-6">

    {{-- BAR ATAS: search + status --}}
    <div class="flex items-center gap-3">
      <div class="relative flex-1">
        <input type="text" wire:model.live.debounce.400ms="q"
               placeholder="Cari Pengguna…"
               class="w-full rounded-xl border border-gray-200 bg-white pl-10 pr-3 py-2.5 focus:outline-none focus:ring" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 102.546 7.032l3.71 3.71a1 1 0 001.415-1.414l-3.71-3.71A4 4 0 008 4z" clip-rule="evenodd"/></svg>
      </div>
      <select wire:model.live="status" class="rounded-xl border border-gray-200 bg-white px-3 py-2.5">
        @foreach($statusOptions as $val => $label)
          <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
      </select>
      <button class="rounded-xl border border-gray-200 bg-white p-2.5" title="Notifikasi">🔔</button>
      <button class="rounded-xl border border-gray-200 bg-white p-2.5" title="Pengaturan">⚙️</button>
      <button class="rounded-xl border border-gray-200 bg-white p-2.5" title="Pesan">✉️</button>
      <div class="rounded-xl border border-gray-200 bg-white px-3 py-2.5">🙂 ▾</div>
    </div>

    {{-- KOTAK JUDUL --}}
    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-5">
      <h2 class="text-lg font-semibold">Pusat Data</h2>
      <p class="text-sm text-gray-500 -mt-0.5">Kelola seluruh data pengguna, bengkel, dan kendaraan di platform</p>
    </div>

    {{-- ACTIONS + FILTER KATEGORI --}}
    <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center">
      <select wire:model.live="category" class="w-44 rounded-xl border border-gray-200 bg-white px-3 py-2.5">
        @foreach($categoryOptions as $val => $label)
          <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
      </select>

      <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-white hover:bg-blue-700">+ Tambah Data</button>
      <button class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 hover:bg-gray-50">Edit</button>
      <button class="rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-rose-600 hover:bg-rose-50">🗑 Hapus</button>

      <div class="ml-auto w-full md:w-80">
        <input type="text" wire:model.live.debounce.400ms="q"
               placeholder="Cari data..."
               class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 focus:outline-none focus:ring" />
      </div>
    </div>

    {{-- WRAPPER TABEL --}}
    <div class="mt-4 rounded-2xl border border-gray-200 bg-white">
      @if(!$category)
        <div class="flex h-72 items-center justify-center text-gray-400">
          Pilih kategori untuk menampilkan data
        </div>
      @else
        @switch($category)
          @case('users')
            @include('livewire.admin.data-center.tables.users', ['rows' => $rows])
          @break

          @case('workshops')
            @include('livewire.admin.data-center.tables.workshops', ['rows' => $rows])
          @break

          @case('vehicles')
            @include('livewire.admin.data-center.tables.vehicles', ['rows' => $rows])
          @break
        @endswitch
      @endif
    </div>

  </div>
</div>
