<div class="mx-auto max-w-5xl px-4 py-6 lg:py-8">

  {{-- Back + Title --}}
  <div class="mb-4 flex items-center justify-between">
    <a href="{{ route('admin.promotions.index') }}"
       class="inline-flex items-center gap-1 text-sm text-neutral-500 hover:text-neutral-700">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
        <path d="M15 19 8 12l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span>Kembali</span>
    </a>
  </div>

  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-neutral-900">Tambah Banner</h1>
    <p class="mt-1 text-sm text-neutral-500">
      Buat banner baru untuk homepage atau halaman produk.
    </p>
  </div>

  <form wire:submit.prevent="save"
        class="space-y-6 rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm">

    @if (session('success'))
      <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
      </div>
    @endif

    {{-- INFORMASI BANNER --}}
    <div>
      <h2 class="text-base font-semibold text-neutral-900">Informasi Banner</h2>
      <p class="mt-1 text-sm text-neutral-500">
        Lengkapi detail banner untuk keperluan promosi.
      </p>
    </div>

    {{-- Upload gambar --}}
    <div class="space-y-2">
      <label class="text-sm font-medium text-neutral-800">
        Gambar Banner <span class="text-red-500">*</span>
      </label>

      <div
        class="relative flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-red-200 bg-red-50/60 px-6 py-10 text-center"
        onclick="document.getElementById('banner-image-input').click()"
      >
        <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-white">
          <svg class="h-7 w-7 text-red-500" viewBox="0 0 24 24" fill="none">
            <path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 4v12M7 9l5-5 5 5"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        <div class="text-sm text-neutral-700">
          <span class="font-medium text-red-600">Klik untuk upload</span> atau drag &amp; drop
        </div>
        <p class="mt-1 text-xs text-neutral-500">
          Format JPG/PNG · Maksimal 5MB · Rasio 2.4:1 (1440×600px minimum)
        </p>

        @if ($image)
          <div class="mt-4">
            <p class="mb-2 text-xs font-medium text-neutral-600">Preview:</p>
            <img src="{{ $image->temporaryUrl() }}"
                 class="max-h-40 rounded-xl border border-neutral-200 object-cover" alt="Preview banner">
          </div>
        @endif
      </div>

      <input id="banner-image-input" type="file" class="hidden"
             wire:model="image" accept="image/*">

      @error('image')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
      @enderror
    </div>

    {{-- Lokasi & status --}}
    <div class="grid gap-4 md:grid-cols-2">
      <div class="space-y-1.5">
        <label class="text-sm font-medium text-neutral-800">
          Lokasi Banner <span class="text-red-500">*</span>
        </label>
        <select wire:model="location"
                class="h-11 w-full rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-800 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400">
          <option value="">Pilih lokasi banner</option>
          @foreach($locationOptions as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
          @endforeach
        </select>
        @error('location')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>

      <div class="space-y-1.5">
        <label class="text-sm font-medium text-neutral-800">
          Status
        </label>
        <select wire:model="status"
                class="h-11 w-full rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-800 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400">
          @foreach($statusOptions as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
          @endforeach
        </select>
        @error('status')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>
    </div>

    {{-- Judul & link --}}
    <div class="space-y-1.5">
      <label class="text-sm font-medium text-neutral-800">
        Judul Banner <span class="text-red-500">*</span>
      </label>
      <input type="text" wire:model.defer="title"
             class="h-11 w-full rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-800 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400"
             placeholder="Contoh: Promo Camping Gear Akhir Tahun">
      @error('title')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
      @enderror
    </div>

    <div class="space-y-1.5">
      <label class="text-sm font-medium text-neutral-800">
        Link Tujuan (Opsional)
      </label>
      <input type="url" wire:model.defer="link"
             class="h-11 w-full rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-800 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400"
             placeholder="https://example.com/atau/path-internal">
      <p class="mt-1 text-xs text-neutral-400">
        Kosongkan jika banner tidak perlu bisa diklik.
      </p>
      @error('link')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
      @enderror
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-end gap-3 pt-2">
      <a href="{{ route('admin.promotions.index') }}"
         class="inline-flex h-10 items-center rounded-xl border border-neutral-200 px-4 text-sm font-medium text-neutral-700 hover:bg-neutral-50">
        Batal
      </a>
      <button type="submit"
              class="inline-flex h-10 items-center rounded-xl bg-red-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1"
              wire:loading.attr="disabled">
        <span wire:loading.remove>Simpan Banner</span>
        <span wire:loading class="inline-flex items-center gap-1">
          <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"/>
            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
          </svg>
          Menyimpan...
        </span>
      </button>
    </div>

  </form>
</div>
