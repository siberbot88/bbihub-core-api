<div wire:key="promotions-root"
     class="w-full max-w-7xl mx-auto px-4 lg:px-6 space-y-5">


  {{-- PAGE TITLE + ACTIONS --}}
  <div class="flex flex-wrap items-center gap-3">
    <div>
      <h1 class="text-2xl font-bold text-neutral-800">Manajemen Banner</h1>
      <div class="text-neutral-500">Kelola banner untuk homepage dan halaman produk</div>
    </div>
    <div class="ms-auto flex items-center gap-2">
      <button wire:click="refresh" type="button"
              class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 hover:bg-neutral-50">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M21 12a9 9 0 1 1-2.64-6.36" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Refresh
      </button>
      <button wire:click="openCreate" type="button"
              class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-white hover:bg-red-700">
        <span class="text-lg leading-none">+</span> Tambah Banner
      </button>
    </div>
  </div>

  {{-- SUMMARY CARDS --}}
  @php
    $limitTotal = $limitTotal ?? 10;
    $limitHome  = $limitHome  ?? 5;
    $limitProd  = $limitProd  ?? 5;

    $countTotal = $countTotal ?? ($promotions->total() ?? 0);
    $countHome  = $countHome  ?? ($homeCount   ?? 0);
    $countProd  = $countProd  ?? ($productCount?? 0);

    $pctTotal = min(100, round(($countTotal/$limitTotal)*100));
    $pctHome  = min(100, round(($countHome/$limitHome)*100));
    $pctProd  = min(100, round(($countProd/$limitProd)*100));
  @endphp

  <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    {{-- Total --}}
    <div class="rounded-2xl border bg-white p-4">
      <div class="flex items-start justify-between">
        <div>
          <div class="text-neutral-500 text-sm">Total Banner</div>
          <div class="mt-1 text-2xl font-bold">{{ $countTotal }}/{{ $limitTotal }}</div>
          <div class="text-xs text-neutral-500">Aktif/Total (max {{ $limitTotal }})</div>
        </div>
        <div class="text-xs text-neutral-400">👁️ {{ $countTotal }}</div>
      </div>
      <div class="mt-3 h-2 w-full overflow-hidden rounded bg-neutral-100">
        <div class="h-full bg-red-500" style="width: {{ $pctTotal }}%"></div>
      </div>
      <div class="mt-1 text-xs text-red-600">Batas maksimal tercapai</div>
    </div>

    {{-- Homepage --}}
    <div class="rounded-2xl border bg-white p-4">
      <div class="flex items-start justify-between">
        <div>
          <div class="text-neutral-500 text-sm">Homepage</div>
          <div class="mt-1 text-2xl font-bold">{{ $countHome }}/{{ $limitHome }}</div>
          <div class="text-xs text-neutral-500">Aktif/Total (max {{ $limitHome }})</div>
        </div>
        <div class="text-xs text-neutral-400">👁️ {{ $countHome }}</div>
      </div>
      <div class="mt-3 h-2 w-full overflow-hidden rounded bg-neutral-100">
        <div class="h-full bg-red-500" style="width: {{ $pctHome }}%"></div>
      </div>
      <div class="mt-1 text-xs text-red-600">Batas maksimal tercapai</div>
    </div>

    {{-- Halaman Produk --}}
    <div class="rounded-2xl border bg-white p-4">
      <div class="flex items-start justify-between">
        <div>
          <div class="text-neutral-500 text-sm">Halaman Produk</div>
          <div class="mt-1 text-2xl font-bold">{{ $countProd }}/{{ $limitProd }}</div>
          <div class="text-xs text-neutral-500">Aktif/Total (max {{ $limitProd }})</div>
        </div>
        <div class="text-xs text-neutral-400">👁️ {{ $countProd }}</div>
      </div>
      <div class="mt-3 h-2 w-full overflow-hidden rounded bg-neutral-100">
        <div class="h-full bg-red-500" style="width: {{ $pctProd }}%"></div>
      </div>
      <div class="mt-1 text-xs text-red-600">Batas maksimal tercapai</div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="rounded-2xl border bg-white p-3">
    <div class="flex flex-wrap items-center gap-3">
      <div class="relative">
        <input type="text" wire:model.live.debounce.300ms="q"
               placeholder="Cari banner…"
               class="h-9 w-72 rounded-lg border-neutral-300 ps-9 focus:border-red-500 focus:ring-red-500"/>
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" viewBox="0 0 24 24" fill="none">
          <path d="M21 21l-4-4M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>

      <select wire:model.live="status"
              class="h-9 rounded-lg border-neutral-300 focus:border-red-500 focus:ring-red-500">
        @foreach(($statusOptions ?? ['all'=>'Semua Status','active'=>'Aktif','draft'=>'Draft','expired'=>'Expired']) as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
      </select>

      <div class="ms-auto flex items-center gap-2">
        <span class="text-sm text-neutral-500">Tampil</span>
        <select wire:model.live="perPage"
                class="h-9 rounded-lg border-neutral-300 focus:border-red-500 focus:ring-red-500">
          @foreach([10,20,30,50] as $n)
            <option value="{{ $n }}">{{ $n }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  {{-- TABS --}}
  @php $placement = $placement ?? 'all'; @endphp
  <div class="flex items-center gap-1">
    @foreach(['all'=>'Semua Banner','home'=>'Homepage','product'=>'Halaman Produk'] as $val=>$label)
      <button wire:click="$set('placement','{{ $val }}')"
              @class([
                'px-4 py-2 rounded-lg text-sm',
                'bg-red-600 text-white' => $placement === $val,
                'text-neutral-600 hover:bg-neutral-100' => $placement !== $val,
              ])>{{ $label }}</button>
    @endforeach
  </div>

  {{-- TABLE / GRID LIST --}}
  <div class="w-full overflow-hidden rounded-2xl border bg-white">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[820px] divide-y divide-neutral-200 text-sm">
        <thead class="bg-neutral-50">
          <tr class="text-neutral-600">
            <th class="px-4 py-3 text-left">Banner</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Penempatan</th>
            <th class="px-4 py-3 text-left">Periode</th>
            <th class="px-4 py-3 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100">
          @forelse($promotions as $p)
            @php
              $st = $p->status ?? 'draft';
              $place = $p->placement ?? 'home'; // 'home' | 'product'
              $img = $p->image_url ?? asset('images/placeholder.svg');
            @endphp
            <tr class="hover:bg-neutral-50">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <img src="{{ $img }}" alt="" class="h-12 w-20 rounded object-cover border">
                  <div>
                    <div class="font-medium">{{ $p->title }}</div>
                    <div class="text-xs text-neutral-500 line-clamp-1">{{ $p->description }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">
                <span @class([
                  'rounded-md px-2 py-0.5 text-xs font-medium',
                  'bg-red-100 text-red-700' => $st === 'active',
                  'bg-red-100 text-red-700'     => $st === 'draft',
                  'bg-red-100 text-red-700'       => $st === 'expired',
                  'bg-neutral-100 text-neutral-700' => !in_array($st, ['active','draft','expired']),
                ])>{{ strtoupper($st) }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="text-neutral-700">
                  {{ $place === 'home' ? 'Homepage' : 'Halaman Produk' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="text-neutral-700">
                  {{ optional($p->starts_at)->format('d M Y') ?? '-' }} — {{ optional($p->ends_at)->format('d M Y') ?? '-' }}
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3 text-neutral-500">
                  <button wire:click="show({{ $p->id }})" class="hover:text-neutral-700" title="Lihat">👁️</button>
                  <button wire:click="edit({{ $p->id }})" class="hover:text-neutral-700" title="Edit">✏️</button>
                  <button wire:click="confirmDelete({{ $p->id }})" class="text-red-600 hover:text-red-700" title="Hapus">⛔</button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-10">
                <div class="flex items-center justify-center text-neutral-500">Tidak ada data.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-end p-3">
      {{ $promotions->onEachSide(1)->links() }}
    </div>
  </div>
</div>
