<div class="min-h-screen bg-[#F8F6F6]">
  <div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

    <div>
      <h1 class="text-2xl font-bold text-neutral-800">Edit Pengguna</h1>
      <p class="text-neutral-500 text-sm">
        Perbarui data pengguna ID: {{ $user->id }}
      </p>
    </div>

    @if (session('success'))
      <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}
      </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-5 bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">

      {{-- Nama --}}
      <div class="space-y-1">
        <label class="block text-sm font-medium text-neutral-700">
          Nama
        </label>
        <input type="text"
               wire:model.defer="name"
               class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-red-500 focus:ring-red-500">
        @error('name')
          <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      {{-- Email --}}
      <div class="space-y-1">
        <label class="block text-sm font-medium text-neutral-700">
          Email
        </label>
        <input type="email"
               wire:model.defer="email"
               class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-red-500 focus:ring-red-500">
        @error('email')
          <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      {{-- Status (opsional) --}}
      @if(!is_null($status))
        <div class="space-y-1">
          <label class="block text-sm font-medium text-neutral-700">
            Status
          </label>
          <select wire:model.defer="status"
                  class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-red-500 focus:ring-red-500">
            <option value="">Pilih status…</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
            <option value="pending">Menunggu verifikasi</option>
          </select>
          @error('status')
            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      @endif

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-100">
        <a href="{{ route('admin.users.index') }}"
           class="rounded-xl border border-neutral-300 bg-white px-4 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50">
          Batal
        </a>
        <button type="submit"
                class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700">
          Simpan Perubahan
        </button>
      </div>
    </form>

  </div>
</div>
 