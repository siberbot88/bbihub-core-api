{{-- ====== DASHBOARD CONTENT ONLY ====== --}}

{{-- HEADER / HERO --}}
<section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-xl font-semibold text-gray-900">
        Selamat Datang, <span class="text-[#E11D48] font-bold">Super Admin</span>
      </h1>
      <p class="mt-1 text-sm text-gray-500">
        Jelajahi dashboard BBI HUB hari ini
      </p>
      <p class="text-xs text-gray-400">
        {{ now()->translatedFormat('l, d F Y') }} · {{ now()->format('H:i') }} WIB
      </p>
    </div>

    <div class="flex gap-2 flex-wrap">
      <button class="rounded-xl bg-[#E11D48] px-4 py-2 text-white text-sm hover:bg-[#be123c] transition">
        Ekspor Data
      </button>
      <button class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
        Filter Data
      </button>
      <button class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
        Refresh
      </button>
    </div>
  </div>
</section>

{{-- KPI CARDS --}}
<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
  @foreach($cards as $c)
    <article class="relative rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition h-[140px]">
      <span class="absolute right-5 top-5 rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-600">
        {{ $c['delta'] }}
      </span>

      <h3 class="text-sm font-semibold text-gray-800">{{ $c['title'] }}</h3>
      <div class="mt-3 flex items-end justify-between">
        <div>
          <div class="text-3xl font-bold text-[#E11D48]">{{ $c['value'] }}</div>
          <p class="mt-1 text-xs text-gray-500">{{ $c['desc'] }}</p>
        </div>
        <div class="h-12 w-12 rounded-xl bg-[#FEF2F2] grid place-items-center">
          <img src="{{ asset('icons/' . $c['icon'] . '.svg') }}" alt="{{ $c['icon'] }}" class="h-5 w-5">
        </div>
      </div>
    </article>
  @endforeach
</section>

{{-- ANTRIAN TINDAKAN CEPAT --}}
<section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
  <header class="mb-3 flex items-center justify-between">
    <h2 class="text-base font-semibold text-gray-900">Antrian Tindakan Cepat</h2>
  </header>

  <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
    <a href="#" class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 hover:bg-yellow-50 transition">
      <span class="h-10 w-10 grid place-content-center rounded-lg bg-yellow-100 text-yellow-700 text-lg">🏷</span>
      <div class="text-sm leading-tight">
        <div class="font-semibold text-gray-900">Bengkel Baru</div>
        <div class="text-xs text-gray-500">Belum Diverifikasi</div>
      </div>
    </a>

    <a href="#" class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 hover:bg-indigo-50 transition">
      <span class="h-10 w-10 grid place-content-center rounded-lg bg-indigo-100 text-indigo-700 text-lg">📣</span>
      <div class="text-sm leading-tight flex-1">
        <div class="font-semibold text-gray-900">Laporan Pengguna</div>
        <div class="text-xs text-gray-500">Laporan Baru</div>
      </div>
      <span class="ml-auto rounded-full bg-indigo-600 px-2 py-0.5 text-xs text-white">10</span>
    </a>

    <a href="#" class="flex items-center gap-3 rounded-xl border border-gray-100 p-3 hover:bg-emerald-50 transition">
      <span class="h-10 w-10 grid place-content-center rounded-lg bg-emerald-100 text-emerald-700 text-lg">🔧</span>
      <div class="text-sm leading-tight">
        <div class="font-semibold text-gray-900">Bengkel Perlu Update</div>
        <div class="text-xs text-gray-500">Data tidak lengkap</div>
      </div>
    </a>
  </div>
</section>

{{-- CHARTS --}}
<section class="grid gap-4 lg:grid-cols-2">
  {{-- Line chart --}}
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="mb-3 flex items-center justify-between">
      <h3 class="text-sm font-semibold text-gray-800">Statistik Servis Perbulan</h3>
      <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs hover:bg-gray-50 transition">
        2 bulan terakhir
      </button>
    </div>
    <div class="h-64 rounded-xl bg-gray-50 p-2">
      <canvas id="svcChart" class="h-full w-full"></canvas>
    </div>
  </div>

  {{-- Bar chart --}}
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="mb-3 flex items-center justify-between">
      <h3 class="text-sm font-semibold text-gray-800">Distribusi Bengkel Berdasarkan Pendapatan</h3>
      <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs hover:bg-gray-50 transition">⋮</button>
    </div>
    <div class="h-64 rounded-xl bg-gray-50 p-2">
      <canvas id="revChart" class="h-full w-full"></canvas>
    </div>
  </div>
</section>

{{-- ACTIVITY LOG --}}
<section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
  <div class="mb-3 flex items-center justify-between">
    <h3 class="text-base font-semibold text-gray-900">Log Aktivitas Terbaru</h3>
    <a href="#" class="text-sm text-[#E11D48] hover:underline">Lihat Semua</a>
  </div>

  <div class="space-y-2">
    @foreach ($activityLogs as $log)
      <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
        <span class="h-8 w-8 rounded-full bg-emerald-100"></span>
        <div class="flex-1">
          <div class="text-sm font-medium text-gray-900">{{ $log['title'] }}</div>
          <div class="text-xs text-gray-500">{{ $log['time'] }}</div>
        </div>
      </div>
    @endforeach
  </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const chartFont = {
    family: getComputedStyle(document.documentElement)
      .getPropertyValue('--tw-font-sans') || 'Inter, ui-sans-serif, system-ui'
  };

  new Chart(document.getElementById('svcChart'), {
    type: 'line',
    data: {
      labels: @json($serviceMonthly['labels'] ?? []),
      datasets: [{
        label: 'Servis Selesai',
        data: @json($serviceMonthly['data'] ?? []),
        borderWidth: 2, tension: .4, pointRadius: 3,
        borderColor: '#E11D48', backgroundColor: 'rgba(225,29,72,.10)', fill: true,
      }]
    },
    options: { plugins:{legend:{display:false}},
      elements:{point:{borderWidth:0}},
      scales:{ y:{beginAtZero:true, grid:{color:'#eee'}, ticks:{font:chartFont}},
               x:{grid:{display:false}, ticks:{font:chartFont}} }
    }
  });

  new Chart(document.getElementById('revChart'), {
    type: 'bar',
    data: {
      labels: @json($revenueByWorkshop['labels'] ?? []),
      datasets: [{
        label:'Revenue',
        data:@json($revenueByWorkshop['data'] ?? []),
        backgroundColor:'#E11D48',
        borderRadius:6,
        borderSkipped:false
      }]
    },
    options: { plugins:{legend:{display:false}},
      scales:{ y:{beginAtZero:true, grid:{color:'#eee'}, ticks:{font:chartFont}},
               x:{grid:{display:false}, ticks:{font:chartFont}} }
    }
  });
</script>
@endpush
