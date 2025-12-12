<div class="min-h-screen">
  <div class="w-full px-2 lg:px-4 space-y-5">

    {{-- JUDUL --}}
    <div>
      <h1 class="text-2xl font-bold text-neutral-800">Pusat Data</h1>
      <div class="text-neutral-500">
        Kelola seluruh data pengguna, bengkel, dan kendaraan di platform
      </div>
    </div>

{{-- ACTIONS + FILTER KATEGORI --}}
<div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center">
  <select
    wire:model.live="category"
    class="w-44 rounded-xl border border-gray-200 bg-white px-3 py-2.5"
  >
    @foreach($categoryOptions as $val => $label)
      <option value="{{ $val }}">{{ $label }}</option>
    @endforeach
  </select>

  @if($category === 'users')
    <a href="{{ route('admin.data-center.create', ['category' => 'users']) }}"
       class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">
      + Tambah Pengguna
    </a>
  @elseif($category === 'workshops')
    <a href="{{ route('admin.data-center.create', ['category' => 'workshops']) }}"
       class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">
      + Tambah Bengkel
    </a>
  @elseif($category === 'promotions')
    <a href="{{ route('admin.data-center.create', ['category' => 'promotions']) }}"
       class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">
      + Tambah Promosi
    </a>
  @else
    <a href="{{ route('admin.data-center.create') }}"
       class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">
      + Tambah Data
    </a>
  @endif

  {{-- EDIT (Livewire) --}}
  <button
    type="button"
    wire:click="editSelected"
    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 hover:bg-gray-50"
  >
    Edit
  </button>

  {{-- HAPUS (Livewire) --}}
  <button
    type="button"
    wire:click="confirmDeleteSelected"
    class="flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-rose-600 hover:bg-rose-50"
  >
    <svg xmlns="http://www.w3.org/2000/svg"
         fill="none"
         viewBox="0 0 24 24"
         stroke-width="1.5"
         stroke="currentColor"
         class="h-5 w-5">
      <path stroke-linecap="round"
            stroke-linejoin="round"
            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21.75H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0V4.5A1.5 1.5 0 0013.75 3h-3.5A1.5 1.5 0 008.75 4.5v1.044" />
    </svg>
    <span>Hapus</span>
  </button>

  <div class="ml-auto w-full md:w-80">
    <input
      type="text"
      wire:model.live.debounce.400ms="q"
      placeholder="Cari data..."
      class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 focus:outline-none focus:ring"
    />
  </div>
</div>

{{-- NOTIF sederhana (kalau belum punya toast) --}}
<script>
  document.addEventListener('livewire:init', () => {
    Livewire.on('notify', ({ message }) => alert(message));
    Livewire.on('confirm-delete', ({ message }) => {
      if (confirm(message)) {
        Livewire.dispatch('do-delete-selected');
      }
    });
  });
</script>

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
          @case('promotions')
            @include('livewire.admin.data-center.tables.promotions', ['rows' => $rows])
          @break
        @endswitch
      @endif
    </div>

    {{-- Modal Detail User (punyamu tadi sudah oke, nggak perlu diubah) --}}
    @if($showDetailModal && $selectedUser)
      {{-- ... modal yang tadi, biarkan seperti itu ... --}}
    @endif

  </div>
</div>
