<div class="min-h-screen">
  <div class="w-full px-2 lg:px-4 space-y-5">

    {{-- BAR ATAS: search + status --}}
    <div class="flex items-center gap-3">
      <div class="relative flex-1">
        <input type="text" wire:model.live.debounce.400ms="q"
               placeholder="Cari Pengguna…"
               class="w-full rounded-xl border border-gray-200 bg-white pl-10 pr-3 py-2.5 focus:outline-none focus:ring" />
        <span class="absolute inset-y-0 left-4 flex items-center text-gray-400 group-focus-within:text-[#DC2626] transition-colors">
            {{-- Heroicon: MagnifyingGlass --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </span>
      </div>
      <select wire:model.live="status" class="rounded-xl border border-gray-200 bg-white px-3 py-2.5">
        @foreach($statusOptions as $val => $label)
          <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
      </select>
      <button class="rounded-xl border border-gray-200 bg-white p-2.5" title="Notifikasi">
          <svg xmlns="http://www.w3.org/2000/svg" 
              fill="none" viewBox="0 0 24 24" 
              stroke-width="1.5" stroke="currentColor" 
              class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M14.857 17.243a2.999 2.999 0 01-5.714 0M12 6v-.75m0 1.5c-3.728 0-6.75 3.022-6.75 6.75v1.102c0 .42-.26.794-.647.942l-1.176.441c-.51.192-.51.928 0 1.12l1.176.44c.387.149.647.523.647.943v1.102c0 3.728 3.022 6.75 6.75 6.75a6.75 6.75 0 006.75-6.75v-1.102c0-.42.26-.794.647-.943l1.176-.44c.51-.192.51-.928 0-1.12l-1.176-.441a1.125 1.125 0 01-.647-.942V12.75c0-3.728-3.022-6.75-6.75-6.75z" />
          </svg>
      </button>
      <button class="rounded-xl border border-gray-200 bg-white p-2.5" title="Pengaturan">
          <svg xmlns="http://www.w3.org/2000/svg" 
              fill="none" viewBox="0 0 24 24" 
              stroke-width="1.5" stroke="currentColor" 
              class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M10.343 3.94c.09-.542.56-.94 1.11-.94s1.02.398 1.11.94l.396 2.37c.07.42.36.764.75.93l2.12.88c.5.21.78.76.63 1.27-.15.51-.65.83-1.18.77l-2.26-.28a1.125 1.125 0 00-1.02.36l-1.58 1.77c-.33.37-.89.37-1.22 0l-1.58-1.77a1.125 1.125 0 00-1.02-.36l-2.26.28c-.53.06-1.03-.27-1.18-.77-.15-.51.13-1.06.63-1.27l2.12-.88c.39-.16.68-.51.75-.93l.396-2.37zM12 15.75a3.75 3.75 0 110-7.5 3.75 3.75 0 010 7.5z" />
          </svg>
      </button>
      <button class="rounded-xl border border-gray-200 bg-white p-2.5" title="Pesan">
          <svg xmlns="http://www.w3.org/2000/svg" 
              fill="none" viewBox="0 0 24 24" 
              stroke-width="1.5" stroke="currentColor" 
              class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21.75 7.5v9.75a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V7.5m19.5 0A2.25 2.25 0 0019.5 5.25H4.5A2.25 2.25 0 002.25 7.5m19.5 0v.208a2.25 2.25 0 01-1.07 1.916l-7.5 4.5a2.25 2.25 0 01-2.16 0l-7.5-4.5A2.25 2.25 0 013 7.708V7.5" />
           </svg>
        </button>
      <div class="flex items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-2.5 cursor-pointer">
          <svg xmlns="http://www.w3.org/2000/svg" 
              fill="none" viewBox="0 0 24 24" 
              stroke-width="1.5" stroke="currentColor" 
              class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 0115 0" />
          </svg>
          <svg xmlns="http://www.w3.org/2000/svg" 
              fill="none" viewBox="0 0 24 24" 
              stroke-width="1.5" stroke="currentColor" 
              class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
          </svg>
      </div>
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

      @if($category === 'users')
        <a href="{{ route('admin.data-center.create', ['category' => 'users']) }}" class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">+ Tambah Pengguna</a>
      @elseif($category === 'workshops')
        <a href="{{ route('admin.data-center.create', ['category' => 'workshops']) }}" class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">+ Tambah Bengkel</a>
      @elseif($category === 'promotions')
        <a href="{{ route('admin.data-center.create', ['category' => 'promotions']) }}" class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">+ Tambah Promosi</a>
      @else
        <a href="{{ route('admin.data-center.create') }}" class="rounded-xl bg-red-600 px-4 py-2.5 text-white hover:bg-red-700">+ Tambah Data</a>
      @endif
      <button id="bulk-edit-btn" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 hover:bg-gray-50">Edit</button>
     <button id="bulk-delete-btn" class="flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-rose-600 hover:bg-rose-50">
          <svg xmlns="http://www.w3.org/2000/svg" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke-width="1.5" 
              stroke="currentColor" 
              class="w-5 h-5">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0115.916 21.75H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0V4.5A1.5 1.5 0 0013.75 3h-3.5A1.5 1.5 0 008.75 4.5v1.044" />
          </svg>
          Hapus
      </button> 

      <div class="ml-auto w-full md:w-80">
        <input type="text" wire:model.live.debounce.400ms="q"
               placeholder="Cari data..."
               class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 focus:outline-none focus:ring" />
      </div>
    </div>

    <script>
      (function(){
        // Edit button
        const editBtn = document.getElementById('bulk-edit-btn');
        if (editBtn) {
          editBtn.addEventListener('click', function(){
            const rows = document.querySelectorAll('.row-checkbox:checked');
            if (!rows || rows.length === 0) {
              alert('Pilih satu baris untuk diedit.');
              return;
            }
            if (rows.length > 1) {
              alert('Pilih hanya satu baris untuk diedit.');
              return;
            }
            const id = rows[0].value;
            const category = document.querySelector('select[wire\\:model\\.live="category"], select[wire\\:model="category"]').value || '{{ $category }}';
            const url = new URL('{{ route('admin.data-center.edit') }}', window.location.origin);
            url.searchParams.set('category', category);
            url.searchParams.set('id', id);
            window.location.href = url.toString();
          });
        }

        // Delete button
        const deleteBtn = document.getElementById('bulk-delete-btn');
        if (deleteBtn) {
          deleteBtn.addEventListener('click', function(){
            const rows = document.querySelectorAll('.row-checkbox:checked');
            if (!rows || rows.length === 0) {
              alert('Pilih minimal satu baris untuk dihapus.');
              return;
            }
            if (!confirm(`Yakin ingin menghapus ${rows.length} item terpilih?`)) {
              return;
            }
            const ids = Array.from(rows).map(r => r.value);
            @this.call('deleteSelected', ids);
          });
        }
      })();
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

    {{-- Modal Detail User --}}
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
                {{ strtoupper(mb_substr($selectedUser->name ?? 'U', 0, 1)) }}
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
                <span class="w-1/3 font-medium text-neutral-600">Login Terakhir</span>
                <span class="text-neutral-800">
                  {{ ($selectedUser->last_login_at ?? $selectedUser->updated_at)
                        ? \Illuminate\Support\Carbon::parse($selectedUser->last_login_at ?? $selectedUser->updated_at)->format('d M Y, H:i')
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
 </div>
