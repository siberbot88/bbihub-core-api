<div class= "w-full px-2 lg:px-4 space-y-5" >
  {{-- Header Section --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Manajemen Bengkel</h1>
      <p class="mt-1 text-sm text-gray-500">Kelola sistem dan data bengkel di platform</p>
    </div>

    <div class="flex gap-3">
      <button class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
        </svg>
        Ekspor
      </button>

      <a href="#" class="flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Tambah Bengkel
      </a>
    </div>
  </div>

  {{-- Filters --}}
  <div class="flex flex-wrap items-center gap-3">
    <div class="relative flex-1 min-w-[250px]">
      <input
        type="text"
        wire:model.live.debounce.400ms="q"
        placeholder="Cari bengkel..."
        class="h-10 w-full rounded-xl border-gray-200 ps-10 text-sm focus:border-red-400 focus:ring-red-400"
      />
      <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
      </span>
    </div>

    <select wire:model.live="status" class="h-10 rounded-xl border-gray-200 text-sm focus:border-red-400 focus:ring-red-400">
      @foreach ($statusOptions as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
      @endforeach
    </select>

    <select wire:model.live="city" class="h-10 rounded-xl border-gray-200 text-sm focus:border-red-400 focus:ring-red-400">
      @foreach ($cityOptions as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
      @endforeach
    </select>
  </div>

  {{-- Summary Cards --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($this->cards as $card)
      <div class="group rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <div class="text-sm text-gray-500">{{ $card['label'] }}</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($card['value']) }}</div>
            <div class="mt-1 text-xs text-emerald-600">{{ $card['hint'] }}</div>
          </div>
          
          @php
            $iconColors = [
              'blue' => 'bg-blue-50 text-blue-600',
              'yellow' => 'bg-yellow-50 text-yellow-600',
              'green' => 'bg-emerald-50 text-emerald-600',
              'red' => 'bg-red-50 text-red-600',
            ];
          @endphp
          
          <div class="h-12 w-12 rounded-xl {{ $iconColors[$card['color']] ?? 'bg-gray-50 text-gray-600' }} flex items-center justify-center transition-transform group-hover:scale-110">
            @if($card['color'] === 'blue')
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" />
              </svg>
            @elseif($card['color'] === 'yellow')
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            @elseif($card['color'] === 'green')
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            @else
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
              </svg>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Table --}}
  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
      <div class="font-semibold text-gray-900">Daftar Bengkel</div>
      <div class="text-sm text-gray-500">Total: {{ $this->workshops->total() }} Bengkel</div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr class="text-gray-600">
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Bengkel</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Lokasi</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Rating</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Bergabung</th>
            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100 bg-white">
          @forelse ($this->workshops as $w)
            <tr class="hover:bg-gray-50 transition-colors">
              {{-- Workshop Info --}}
              <td class="px-6 py-4">
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 shrink-0 rounded-xl bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center">
                    <span class="text-sm font-bold text-red-700">{{ strtoupper(mb_substr($w->name ?? 'W', 0, 1)) }}</span>
                  </div>
                  <div>
                    <div class="font-semibold text-gray-900">{{ $w->name }}</div>
                    <div class="text-xs text-gray-500">ID: {{ $w->code }}</div>
                  </div>
                </div>
              </td>

              {{-- Status --}}
              <td class="px-6 py-4">
                @php
                  $statusMap = [
                    'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Pending'],
                    'active' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Aktif'],
                    'suspended' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Suspended'],
                  ];
                  $status = $w->status ?? 'pending'; // Default to pending if status doesn't exist
                  $st = $statusMap[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => ucfirst($status)];
                @endphp
                <span class="inline-flex items-center gap-1.5 rounded-full {{ $st['bg'] }} px-3 py-1 text-xs font-medium {{ $st['text'] }}">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                  </svg>
                  {{ $st['label'] }}
                </span>
              </td>

              {{-- Lokasi --}}
              <td class="px-6 py-4">
                <div class="flex items-center gap-2 text-gray-700">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-gray-400">
                    <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                  </svg>
                  {{ $w->city ?? '-' }}
                </div>
              </td>

              {{-- Rating --}}
              <td class="px-6 py-4">
                <div class="inline-flex items-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-yellow-400">
                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                  </svg>
                  <span class="font-medium text-gray-900">{{ $w->rating ? number_format($w->rating, 1) : '-' }}</span>
                </div>
              </td>

              {{-- Bergabung --}}
              <td class="px-6 py-4 text-gray-600">
                {{ $w->created_at ? $w->created_at->diffForHumans() : '-' }}
              </td>

              {{-- Actions --}}
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button wire:click="view('{{ $w->id }}')" class="rounded-lg p-2 text-blue-600 hover:bg-blue-50 transition-colors" title="Lihat Detail">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                      <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <button wire:click="edit('{{ $w->id }}')" class="rounded-lg p-2 text-orange-600 hover:bg-orange-50 transition-colors" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
                      <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                    </svg>
                  </button>

                  <button wire:click="suspend('{{ $w->id }}')" class="rounded-lg p-2 text-purple-600 hover:bg-purple-50 transition-colors" title="Suspend">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM6.75 9.25a.75.75 0 000 1.5h6.5a.75.75 0 000-1.5h-6.5z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <button wire:click="delete('{{ $w->id }}')" class="rounded-lg p-2 text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center gap-3 text-gray-400">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" />
                  </svg>
                  <p class="font-medium">Tidak ada bengkel ditemukan</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-between border-t border-gray-100 px-6 py-4">
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-500">Tampil</span>
        <select wire:model.live="perPage" class="h-9 rounded-lg border-gray-300 text-sm focus:border-red-400 focus:ring-red-400">
          @foreach ([10, 20, 30, 50] as $n)
            <option value="{{ $n }}">{{ $n }}</option>
          @endforeach
        </select>
        <span class="text-sm text-gray-500">baris</span>
      </div>
      <div>
        {{ $this->workshops->onEachSide(1)->links() }}
      </div>
    </div>
  </div>

  @include('livewire.admin.workshops.modals')
</div>
