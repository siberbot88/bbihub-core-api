<div class="p-6">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 1.657-1.567 3-3.5 3S5 12.657 5 11 6.567 8 8.5 8 12 9.343 12 11zM12 11v9" />
                </svg>
                <div>
                    <h2 class="text-xl font-bold">Tambah Data — Pusat Data</h2>
                    <p class="text-sm text-gray-500">Buat data baru sesuai kategori dan peran pengguna</p>
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mt-4 p-3 bg-emerald-100 text-emerald-800 rounded">{{ session('message') }}</div>
        @endif

        <form wire:submit.prevent="save" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select wire:model="category" class="block w-full rounded-md border-gray-200 shadow-sm focus:ring-red-200 focus:border-red-300 px-3 py-2">
                    <option value="">Pilih Kategori</option>
                    <option value="users">Pengguna</option>
                    <option value="workshops">Bengkel</option>
                    <option value="promotions">Promosi</option>
                </select>
            </div>

        <!-- @if($category === 'users')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select wire:model.defer="role" class="block w-full rounded-md border-gray-200 shadow-sm focus:ring-red-200 focus:border-red-300 px-3 py-2">
                    <option value="">Pilih Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r['name'] }}">{{ ucwords(str_replace(['-','_'], ' ', $r['name'])) }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Pilih peran pengguna pada dashboard (super-admin, owner, admin).</p>
                @error('role') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                @php
                    $needsWorkshop = in_array($role, ['owner']);
                @endphp
                <label class="block text-sm font-medium text-gray-700 mb-1">Bengkel {{ $needsWorkshop ? '(Wajib)' : '(Optional)' }}</label>
                <select wire:model.defer="workshop_id" class="block w-full rounded-md border-gray-200 shadow-sm focus:ring-red-200 focus:border-red-300 px-3 py-2">
                    <option value="">Pilih Bengkel</option>
                    @foreach($workshops as $w)
                        <option value="{{ $w['id'] }}">{{ $w['name'] }}</option>
                    @endforeach
                </select>
                @error('workshop_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            @if($role === 'owner')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pemilik (optional)</label>
                    <input type="text" wire:model.defer="specialist" class="block w-full rounded-md border-gray-200 shadow-sm px-3 py-2" placeholder="Contoh: Pemilik cabang A" />
                    @error('specialist') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            @endif

            <div class="mb-4">
                <label class="block font-medium mb-1">Nama Lengkap</label>
                <input 
                    type="text" 
                    wire:model.defer="name"
                    class="border px-3 py-2 w-full rounded"
                >
                @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Email</label>
                <input 
                    type="email" 
                    wire:model.defer="email"
                    class="border px-3 py-2 w-full rounded"
                >
                @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Password</label>
                <input 
                    type="password" 
                    wire:model.defer="password"
                    class="border px-3 py-2 w-full rounded"
                >
                @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        @elseif($category === 'workshops')
            <div class="mb-4">
                <label class="block font-medium mb-1">Nama Bengkel</label>
                <input 
                    type="text" 
                    wire:model.defer="workshop_name"
                    class="border px-3 py-2 w-full rounded"
                >
                @error('workshop_name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Kode Bengkel</label>
                <input 
                    type="text" 
                    wire:model.defer="workshop_code"
                    class="border px-3 py-2 w-full rounded"
                >
                @error('workshop_code') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        @endif -->

        <div class="flex items-center gap-3">
            <button class="rounded-xl bg-red-600 px-4 py-2 text-white hover:bg-red-700">Simpan</button>
            <button onclick="window.location.href='{{ route('admin.data-center') }}'">Kembali</button>
            <!-- <a href="{{ route('admin.data-center') }}" class="text-sm text-gray-500 hover:text-gray-700">Tutup ✕</a> -->

        </div>
    </form>
</div>
