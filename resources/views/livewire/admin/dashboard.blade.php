<div id="dashboard-root" class="space-y-6">

  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div>
        <div class="text-lg">Selamat Datang, <span class="font-semibold text-red-600">Super Admin</span></div>
        <div class="text-sm text-gray-500">Jelajahi dashboard BBI HUB hari ini</div>
        <div class="mt-1 text-xs text-gray-400">{{ now()->translatedFormat('l, d F Y') }} · {{ now()->format('H:i') }} WIB</div>
      </div>
      <div class="flex gap-2">
        <button type="button" class="rounded-xl bg-red-600 text-white px-4 py-2.5 hover:bg-red-700">Ekspor Data</button>
        <button type="button" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 hover:bg-gray-50">Filter Data</button>
        <button type="button" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 hover:bg-gray-50">Refresh</button>
      </div>
    </div>
  </div>

  {{-- GRID KPI --}}
  <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @php
      $cards = [
        ['title'=>'Total Bengkel','value'=>20,'desc'=>'Bengkel terkontribusi','delta'=>'+5%','icon'=>'bengkel'],
        ['title'=>'Total User','value'=>120,'desc'=>'Pengguna terdaftar','delta'=>'+5%','icon'=>'pengguna'],
        ['title'=>'Total Teknisi','value'=>45,'desc'=>'Mekanik tersertifikasi','delta'=>'+5%','icon'=>'tech'],
        ['title'=>'Total Feedback','value'=>23,'desc'=>'Feedback hari ini','delta'=>'+5%','icon'=>'feedback'],
      ];
    @endphp

    @foreach($cards as $c)
      <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm border border-gray-100">
        <span class="absolute right-4 top-4 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
          {{ $c['delta'] }}
        </span>

        <div class="text-[15px] font-semibold text-gray-900">{{ $c['title'] }}</div>
        <div class="mt-2 flex items-end justify-between">
          <div>
            <div class="text-4xl font-bold text-red-600 leading-none">{{ $c['value'] }}</div>
            <div class="mt-1 text-sm text-gray-500">{{ $c['desc'] }}</div>
          </div>

          <div class="h-12 w-12 shrink-0 rounded-xl bg-red-50 flex items-center justify-center">
            <x-svg :name="$c['icon']" class="h-6 w-6 text-red-600" />
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Antrian Tindakan Cepat --}}
  <div class="bg-white rounded-2xl border shadow-sm p-4">
    <div class="flex items-center justify-between mb-3">
      <div class="font-semibold">Antrian Tindakan Cepat</div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
      <a href="#" role="button" class="flex items-center gap-3 rounded-xl border p-3 hover:bg-yellow-50">
        <span class="h-10 w-10 grid place-content-center rounded-lg bg-yellow-100 text-yellow-700">🏷</span>
        <div class="text-sm">
          <div class="font-semibold">Bengkel Baru</div>
          <div class="text-[11px] text-neutral-500">Belum Diverifikasi</div>
        </div>
      </a>
      <a href="#" role="button" class="flex items-center gap-3 rounded-xl border p-3 hover:bg-indigo-50">
        <span class="h-10 w-10 grid place-content-center rounded-lg bg-indigo-100 text-indigo-700">📣</span>
        <div class="text-sm">
          <div class="font-semibold">Laporan Pengguna</div>
          <div class="text-[11px] text-neutral-500">Laporan Baru</div>
        </div>
        <span class="ml-auto text-xs bg-indigo-600 text-white px-2 py-0.5 rounded-full">10</span>
      </a>
      <a href="#" role="button" class="flex items-center gap-3 rounded-xl border p-3 hover:bg-emerald-50">
        <span class="h-10 w-10 grid place-content-center rounded-lg bg-emerald-100 text-emerald-700">🔧</span>
        <div class="text-sm">
          <div class="font-semibold">Bengkel Perlu Update</div>
          <div class="text-[11px] text-neutral-500">Data tidak lengkap</div>
        </div>
      </a>
    </div>
  </div>

  <div class="mt-6 grid gap-4 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="mb-3 flex items-center justify-between">
        <div class="font-semibold">Statistik Servis Perbulan</div>
        <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm hover:bg-gray-50">2 bulan terakhir</button>
      </div>
      <div id="svcChart" class="h-64 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
        (Line Chart di sini)
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="mb-3 flex items-center justify-between">
        <div class="font-semibold">Distribusi Bengkel Berdasarkan Tren Pendapatan</div>
        <button type="button" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm hover:bg-gray-50">⋮</button>
      </div>
      <div id="revChart" class="h-64 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
        (Bar Chart di sini)
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border shadow-sm p-4">
    <div class="flex items-center justify-between mb-3">
      <div class="font-semibold">Log Aktivitas Terbaru</div>
      <a href="#" class="text-sm text-red-600 hover:underline">Lihat Semua</a>
    </div>

    <div class="space-y-3">
      @foreach ($activityLogs as $log)
        <div class="rounded-xl border bg-neutral-50">
          <div class="flex items-center gap-3 px-4 py-3">
            <span class="h-8 w-8 rounded-full bg-emerald-100"></span>
            <div class="flex-1">
              <div class="text-sm font-medium">{{ $log['title'] }}</div>
              <div class="text-[11px] text-neutral-500">{{ $log['time'] }}</div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

@push('scripts')
  {{-- CDN Chart.js --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Render ulang saat pertama kali muat & setiap navigasi Livewire (SPA)
    document.addEventListener('DOMContentLoaded', renderCharts, { once: true });
    document.addEventListener('livewire:navigated', renderCharts);

    function renderCharts() {
      // Destroy instance lama agar tidak dobel saat kembali ke halaman
      if (window.svcChartInstance) window.svcChartInstance.destroy();
      if (window.revChartInstance) window.revChartInstance.destroy();

      // ---- Line Chart: Statistik Servis Perbulan ----
      const svcWrap = document.getElementById('svcChart');
      if (svcWrap) {
        const canvas1 = document.createElement('canvas');
        svcWrap.innerHTML = '';
        svcWrap.appendChild(canvas1);

        window.svcChartInstance = new Chart(canvas1, {
          type: 'line',
          data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
              label: 'Jumlah Servis',
              data: [30, 45, 40, 55, 60, 70],
              borderColor: '#ef4444',
              backgroundColor: 'rgba(239, 68, 68, 0.2)',
              borderWidth: 2,
              tension: 0.4,
              fill: true,
            }]
          },
          options: {
            plugins: { legend: { display: false } },
            scales: {
              x: { grid: { display: false } },
              y: { beginAtZero: true }
            }
          }
        });
      }

      // ---- Bar Chart: Distribusi Bengkel ----
      const revWrap = document.getElementById('revChart');
      if (revWrap) {
        const canvas2 = document.createElement('canvas');
        revWrap.innerHTML = '';
        revWrap.appendChild(canvas2);

        window.revChartInstance = new Chart(canvas2, {
          type: 'bar',
          data: {
            labels: ['Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Medan'],
            datasets: [{
              label: 'Pendapatan (juta)',
              data: [120, 90, 60, 80, 50],
              backgroundColor: ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6'],
              borderRadius: 8,
            }]
          },
          options: {
            plugins: { legend: { display: false } },
            scales: {
              x: { grid: { display: false } },
              y: { beginAtZero: true }
            }
          }
        });
      }
    }
  </script>
@endpush
