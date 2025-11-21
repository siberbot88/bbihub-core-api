<div class="space-y-6">

  {{-- 🧭 TOP BAR --}}
  <div class="flex flex-col gap-3 md:flex-row md:items-center">
    <div class="flex-1 relative">
      <input 
        type="text"
        wire:model.live.debounce.400ms="q"
        placeholder="Cari bengkel..."
        class="w-full rounded-xl border border-gray-200 bg-white pl-10 pr-3 py-2.5 text-sm focus:border-red-400 focus:ring focus:ring-red-100"
      />
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M8 4a4 4 0 102.546 7.032l3.71 3.71a1 1 0 001.415-1.414l-3.71-3.71A4 4 0 008 4z" clip-rule="evenodd"/>
      </svg>
    </div>

    <select wire:model.live="status" class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-red-400 focus:ring focus:ring-red-100">
      @foreach($statusOptions as $v => $label)
        <option value="{{ $v }}">{{ $label }}</option>
      @endforeach
    </select>

    <select wire:model.live="city" class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-red-400 focus:ring focus:ring-red-100">
      @foreach($cityOptions as $v => $label)
        <option value="{{ $v }}">{{ $label }}</option>
      @endforeach
    </select>

    <div class="ml-auto flex gap-2">
      <button class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm hover:bg-gray-50">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 12h6m-7 8h8a2 2 0 002-2V9l-5-5H9a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        Ekspor
      </button>

      <a href="#" class="inline-flex items-center rounded-xl bg-red-400 px-4 py-2.5 text-sm text-white shadow hover:bg-red-400">
        + Tambah Bengkel
      </a>
    </div>
  </div>

  {{-- 🧱 HEADER --}}
  <div>
    <h2 class="text-xl font-semibold">Manajemen Bengkel</h2>
    <p class="text-sm text-gray-500 -mt-0.5">Kelola sistem dan data bengkel di platform</p>
  </div>

  {{-- 📊 CARDS --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @php
      $cardsMeta = [
        ['label'=>'Total Bengkel','value'=>$cards['total'] ?? 0,'icon'=>'total_bengkel','delta'=>'+5%'],
        ['label'=>'Menunggu Verifikasi','value'=>$cards['pending'] ?? 0,'icon'=>'total_verifikasi','delta'=>'+2%'],
        ['label'=>'Bengkel Aktif','value'=>$cards['active'] ?? 0,'icon'=>'akun_aktif','delta'=>'+5%'],
        ['label'=>'Bengkel Ditangguhkan','value'=>$cards['suspended'] ?? 0,'icon'=>'akun_nonaktif','delta'=>'+5%'],
      ];
    @endphp

    @foreach($cardsMeta as $c)
      <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="text-sm text-gray-500">{{ $c['label'] }}</div>
        <div class="mt-2 flex items-center justify-between">
          <div class="text-3xl font-semibold text-gray-900">{{ $c['value'] }}</div>
          <div class="h-8 w-8 shrink-0">
            <img src="{{ asset('icons/'.$c['icon'].'.svg') }}" 
                 alt="{{ $c['label'] }}" 
                 class="h-full w-full object-contain" />
          </div>
        </div>
        <div class="mt-1 text-xs text-emerald-600">update {{ $c['delta'] }}</div>
      </div>
    @endforeach
  </div>

  {{-- 📋 TABLE --}}
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50 text-left text-sm text-gray-600">
          <tr>
            <th class="px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300"></th>
            <th class="px-4 py-3">BENGKEL</th>
            <th class="px-4 py-3">STATUS</th>
            <th class="px-4 py-3">LOKASI</th>
            <th class="px-4 py-3">RATING</th>
            <th class="px-4 py-3">MEKANIK</th>
            <th class="px-4 py-3">BERGABUNG</th>
            <th class="px-4 py-3 text-center w-40">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm">
          @forelse($rows as $w)
            <tr class="hover:bg-gray-50/60">
              <td class="px-4 py-4"><input type="checkbox" class="rounded border-gray-300"></td>
              <td class="px-4 py-4">
                <div class="flex items-start gap-3">
                  <div class="h-8 w-8 rounded-lg bg-gray-200 flex items-center justify-center">
                    <img src="{{ asset('icons/total_bengkel.svg') }}" class="h-5 w-5" alt="icon">
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ $w->name }}</div>
                    <div class="text-xs text-gray-500">ID: {{ $w->code }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4">
                @php
                  $map = [
                    'pending'   => 'bg-yellow-100 text-yellow-700',
                    'active'    => 'bg-emerald-100 text-emerald-700',
                    'suspended' => 'bg-rose-100 text-rose-700',
                  ];
                @endphp
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $map[$w->status] ?? 'bg-gray-100 text-gray-700' }}">
                  {{ ucfirst($w->status) }}
                </span>
              </td>
              <td class="px-4 py-4 text-gray-700">{{ $w->city }}</td>
              <td class="px-4 py-4">
                <div class="inline-flex items-center gap-1">
                  <span>⭐</span>
                  <span class="font-medium">{{ $w->rating ? number_format($w->rating,1) : '-' }}</span>
                </div>
              </td>
              <td class="px-4 py-4">{{ $w->mechanics_count ?? '-' }}</td>
              <td class="px-4 py-4 text-gray-500">
                {{ $w->joined_at ? \Illuminate\Support\Carbon::parse($w->joined_at)->diffForHumans() : '-' }}
              </td>
              <td class="px-4 py-4">
                <div class="flex items-center justify-end gap-3 text-[15px]">
                  <button class="hover:opacity-80" title="Detail">
                    <img src="{{ asset('icons/aksi_detail.svg') }}" class="h-5 w-5" alt="Detail">
                  </button>
                  <button class="hover:opacity-80" title="Edit">
                    <img src="{{ asset('icons/aksi_edit.svg') }}" class="h-5 w-5" alt="Edit">
                  </button>
                  <button class="hover:opacity-80" title="Reset Password">
                    <img src="{{ asset('icons/aksi_resetpassword.svg') }}" class="h-5 w-5" alt="Reset Password">
                  </button>
                  <button class="hover:opacity-80" title="Hapus">
                    <img src="{{ asset('icons/aksi_hapus.svg') }}" class="h-5 w-5" alt="Hapus">
                  </button>
                  <button class="hover:opacity-80" title="Tangguhkan">
                    <img src="{{ asset('icons/aksi_tangguhkan.svg') }}" class="h-5 w-5" alt="Tangguhkan">
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-4 py-10 text-center text-gray-500">Tidak ada data.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex flex-col gap-3 p-4 md:flex-row md:items-center md:justify-between">
      <div class="text-sm text-gray-500">
        Menampilkan {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }} hasil
      </div>
      <div class="flex items-center gap-2">
        {{ $rows->onEachSide(1)->links() }}
      </div>
    </div>
  </div>

</div>
