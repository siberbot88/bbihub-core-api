<div id="dashboard-root" class="space-y-6">

  <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <div class="text-2xl font-bold text-gray-900">Selamat Datang, <span class="text-red-600">Super Admin</span></div>
        <div class="mt-1 text-sm text-gray-500">Jelajahi dashboard BBI HUB hari ini</div>
        <div class="mt-2 flex items-center gap-2 text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
            </svg>
            {{ now()->translatedFormat('l, d F Y') }} · {{ now()->format('H:i') }} WIB
        </div>
      </div>
      <div class="flex gap-3">
        <button type="button" class="flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            Ekspor Data
        </button>
        <button type="button" class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
            </svg>
            Filter
        </button>
        <button type="button" class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Refresh
        </button>
      </div>
    </div>
  </div>

  {{-- GRID KPI --}}
  {{-- GRID KPI --}}
  <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @foreach($cards as $index => $c)
      <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg group">
        {{-- Background Decoration --}}
        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-gray-50 opacity-50 transition-transform group-hover:scale-110"></div>

        <div class="relative z-10 flex justify-between items-start">
            <div>
                <div class="text-sm font-medium text-gray-500">{{ $c['title'] }}</div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-gray-900">{{ $c['value'] }}</span>
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                        </svg>
                        {{ $c['delta'] }}
                    </span>
                </div>
                <div class="mt-1 text-xs text-gray-400">{{ $c['desc'] }}</div>
            </div>
            
            <div class="p-3 rounded-xl {{ $index == 0 ? 'bg-blue-50 text-blue-600' : ($index == 1 ? 'bg-purple-50 text-purple-600' : ($index == 2 ? 'bg-orange-50 text-orange-600' : 'bg-rose-50 text-rose-600')) }}">
                @if($c['icon'] == 'bengkel')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                    </svg>
                @elseif($c['icon'] == 'pengguna')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                @elseif($c['icon'] == 'tech')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                    </svg>
                @elseif($c['icon'] == 'feedback')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                    </svg>
                @endif
            </div>
        </div>

        {{-- Sparkline Chart Container --}}
        <div class="mt-4 h-10 w-full">
            <canvas id="sparkline-{{ $index }}"></canvas>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Antrian Tindakan Cepat --}}
  {{-- Antrian Tindakan Cepat --}}
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
      <div class="text-lg font-bold text-gray-900">Antrian Tindakan Cepat</div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
      <a href="#" role="button" class="flex items-center gap-4 rounded-xl border border-gray-100 p-4 hover:bg-yellow-50 hover:border-yellow-200 transition-all group">
        <div class="h-12 w-12 grid place-content-center rounded-xl bg-yellow-100 text-yellow-600 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
            </svg>
        </div>
        <div>
          <div class="font-semibold text-gray-900">Bengkel Baru</div>
          <div class="text-xs text-gray-500">Belum Diverifikasi</div>
        </div>
      </a>
      <a href="#" role="button" class="flex items-center gap-4 rounded-xl border border-gray-100 p-4 hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
        <div class="h-12 w-12 grid place-content-center rounded-xl bg-indigo-100 text-indigo-600 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.467a23.849 23.849 0 010 3.467m0-3.467a23.849 23.849 0 000 3.467" />
            </svg>
        </div>
        <div class="flex-1">
          <div class="font-semibold text-gray-900">Laporan</div>
          <div class="text-xs text-gray-500">Laporan Baru</div>
        </div>
        <span class="text-xs font-bold bg-indigo-600 text-white px-2.5 py-1 rounded-full shadow-sm">10</span>
      </a>
      <a href="#" role="button" class="flex items-center gap-4 rounded-xl border border-gray-100 p-4 hover:bg-emerald-50 hover:border-emerald-200 transition-all group">
        <div class="h-12 w-12 grid place-content-center rounded-xl bg-emerald-100 text-emerald-600 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
        </div>
        <div>
          <div class="font-semibold text-gray-900">Bengkel Update</div>
          <div class="text-xs text-gray-500">Data tidak lengkap</div>
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

  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
      <div class="text-lg font-bold text-gray-900">Log Aktivitas Terbaru</div>
      <a href="#" class="text-sm font-medium text-red-600 hover:text-red-700 hover:underline">Lihat Semua</a>
    </div>

    <div class="space-y-3">
      @foreach ($activityLogs as $log)
        <div class="rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-sm transition-all duration-200">
          <div class="flex items-center gap-4 px-4 py-3">
            <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1">
              <div class="text-sm font-medium text-gray-900">{{ $log['title'] }}</div>
              <div class="text-xs text-gray-500 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                </svg>
                {{ $log['time'] }}
              </div>
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
      
      // Destroy Sparklines
      if (window.sparklineInstances) {
        window.sparklineInstances.forEach(chart => chart.destroy());
      }
      window.sparklineInstances = [];

      // ---- Sparklines ----
      const cardsData = @json($cards);
      
      cardsData.forEach((card, index) => {
        const ctx = document.getElementById(`sparkline-${index}`);
        if (ctx) {
            const color = index === 0 ? '#2563EB' : (index === 1 ? '#9333EA' : (index === 2 ? '#EA580C' : '#E11D48'));
            const bg = index === 0 ? 'rgba(37, 99, 235, 0.1)' : (index === 1 ? 'rgba(147, 51, 234, 0.1)' : (index === 2 ? 'rgba(234, 88, 12, 0.1)' : 'rgba(225, 29, 72, 0.1)'));

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: card.chart.map((_, i) => i), // Dummy labels
                    datasets: [{
                        data: card.chart,
                        borderColor: color,
                        backgroundColor: bg,
                        borderWidth: 2,
                        pointRadius: 0, // Hide points
                        pointHoverRadius: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: {
                        x: { display: false },
                        y: { display: false, min: Math.min(...card.chart) - 5 }
                    },
                    layout: { padding: 0 }
                }
            });
            window.sparklineInstances.push(chart);
        }
      });

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
