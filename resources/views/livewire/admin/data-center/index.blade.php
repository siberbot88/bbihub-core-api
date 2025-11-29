<div class="min-h-screen">
  <div class="mx-auto max-w-7xl px-4 py-6">

    {{-- BAR ATAS: search + status --}}
    <div class="flex items-center gap-3">
      <div class="relative flex-1">
        <input type="text" wire:model.live.debounce.400ms="q"
               placeholder="Cari Pengguna…"
               class="w-full rounded-xl border border-gray-200 bg-white pl-3 pr-3 py-2.5 focus:outline-none focus:ring" />
      </div>

      <select wire:model.live="status" class="rounded-xl border border-gray-200 bg-white px-3 py-2.5">
        @foreach($statusOptions as $val => $label)
          <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
      </select>
    </div>

    {{-- KOTAK JUDUL --}}
    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-5">
      <h2 class="text-lg font-semibold">Pusat Data</h2>
      <p class="text-sm text-gray-500 -mt-0.5">
        Kelola seluruh data pengguna, bengkel, dan kendaraan di platform
      </p>
    </div>

    {{-- ACTIONS + FILTER KATEGORI --}}
    <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center">
      <select wire:model.live="category" class="w-44 rounded-xl border border-gray-200 bg-white px-3 py-2.5">
        @foreach($categoryOptions as $val => $label)
          <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
      </select>

      <button class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">+ Tambah Data</button>
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

  {{-- Debug Info --}}
  <div class="fixed bottom-4 right-4 bg-yellow-100 p-4 rounded shadow text-xs z-50">
    <div>showDetailModal: {{ $showDetailModal ? 'true' : 'false' }}</div>
    <div>selectedUser: {{ $selectedUser ? $selectedUser->name : 'null' }}</div>
    <div>category: {{ $category ?: '(kosong)' }}</div>
  </div>

  {{-- Modal Detail User (CUMA 1 DI SINI) --}}
  @if($showDetailModal && $selectedUser)
    <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50" wire:click="closeDetail">
      <div class="w-full max-w-2xl rounded-lg bg-white shadow-xl" wire:click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between border-b px-6 py-4">
          <h3 class="text-lg font-semibold text-neutral-800">Detail Pengguna</h3>
          <button wire:click="closeDetail" class="text-neutral-400 hover:text-neutral-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        {{-- Content --}}
        <div class="px-6 py-6">
          <div class="mb-6 flex items-center gap-4">
            <div class="grid h-16 w-16 place-items-center rounded-full bg-neutral-200 text-2xl font-semibold text-neutral-700">
              {{ strtoupper(mb_substr($selectedUser->name ?? 'U',0,1)) }}
            </div>
            <div>
              <h4 class="text-xl font-semibold text-neutral-800">{{ $selectedUser->name }}</h4>
              <p class="text-sm text-neutral-500">{{ $selectedUser->email }}</p>
            </div>
          </div>

          <div class="grid gap-4">
            <div class="flex border-b pb-3">
              <span class="w-1/3 font-medium text-neutral-600">ID</span>
              <span class="text-neutral-800">{{ $selectedUser->id }}</span>
            </div>

            <div class="flex border-b pb-3">
              <span class="w-1/3 font-medium text-neutral-600">Status</span>
              <div>
                @php
                  $active = isset($selectedUser->status)
                    ? $selectedUser->status === 'active'
                    : (bool) ($selectedUser->email_verified_at ?? false);
                @endphp
                <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                  {{ $active ? 'AKTIF' : 'Nonaktif' }}
                </span>
              </div>
            </div>

            <div class="flex border-b pb-3">
              <span class="w-1/3 font-medium text-neutral-600">Email Terverifikasi</span>
              <span class="text-neutral-800">
                {{ $selectedUser->email_verified_at
                    ? \Illuminate\Support\Carbon::parse($selectedUser->email_verified_at)->format('d M Y, H:i')
                    : 'Belum terverifikasi' }}
              </span>
            </div>

            <div class="flex border-b pb-3">
              <span class="w-1/3 font-medium text-neutral-600">Login Terakhir</span>
              <span class="text-neutral-800">
                {{ ($selectedUser->last_login_at ?? $selectedUser->updated_at)
                    ? \Illuminate\Support\Carbon::parse($selectedUser->last_login_at ?? $selectedUser->updated_at)->format('d M Y, H:i')
                    : '-' }}
              </span>
            </div>

            <div class="flex border-b pb-3">
              <span class="w-1/3 font-medium text-neutral-600">Dibuat</span>
              <span class="text-neutral-800">
                {{ $selectedUser->created_at
                    ? \Illuminate\Support\Carbon::parse($selectedUser->created_at)->format('d M Y, H:i')
                    : '-' }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 border-t px-6 py-4">
          <button wire:click="closeDetail"
                  class="rounded-md bg-neutral-100 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-200 transition-colors">
            Tutup
          </button>
        </div>
      </div>
    </div>
  @endif
</div>
