<div class="space-y-8" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 500)">
    {{-- Header & Controls --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Executive Decision Intelligence</h1>
            <p class="text-sm text-gray-500">Pemantauan kinerja bisnis real-time berbasis prinsip EIS.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            {{-- Export Button --}}
            <button wire:click="exportData" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Ekspor Data
            </button>

            {{-- Filter Button --}}
            <div class="relative" x-data="{ open: @entangle('showFilter') }">
                <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>

                {{-- Filter Dropdown --}}
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 p-4 z-50 origin-top-right" style="display: none;">
                    <h3 class="font-bold text-gray-900 mb-3">Pilih Periode</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                            <select wire:model="selectedMonth" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                            <select wire:model="selectedYear" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach(range(date('Y'), date('Y')-2) as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button wire:click="applyFilter" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>

            {{-- Refresh Button --}}
            <button wire:click="refresh" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm group">
                <svg class="w-4 h-4 text-gray-500 group-hover:rotate-180 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- Loading Skeleton --}}
    <div wire:loading class="w-full">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 animate-pulse">
            <div class="h-40 bg-gray-200 rounded-2xl"></div>
            <div class="h-40 bg-gray-200 rounded-2xl"></div>
            <div class="h-40 bg-gray-200 rounded-2xl"></div>
        </div>
    </div>

    {{-- 1. KPI Scorecard (Dashboard Style) --}}
    {{-- Grid change: lg:grid-cols-3 to make cards wider as requested --}}
    <div wire:loading.remove class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3" 
         x-show="loaded" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100">
         
        @foreach($scorecard as $index => $card)
            @php
                // Design Config based on ID
                switch ($card['id']) {
                    case 'revenue': $color = 'emerald'; $icon = 'banknotes'; break;
                    case 'mrr': $color = 'blue'; $icon = 'arrow-path'; break;
                    case 'subscriptions': $color = 'cyan'; $icon = 'ticket'; break;
                    case 'users': $color = 'violet'; $icon = 'users'; break;
                    case 'csat': $color = 'amber'; $icon = 'star'; break;
                    case 'nps': $color = 'rose'; $icon = 'megaphone'; break;
                    default: $color = 'gray'; $icon = 'chart-bar'; break;
                }
                
                // Tailwind classes map
                $bgClass = "bg-{$color}-50";
                $textClass = "text-{$color}-600";
                
                // Tooltip Positioning Logic (3-column grid)
                // If it's the 3rd item in a row (index 2, 5, 8...), align to right.
                // Or if it's the last item in a row on smaller screens.
                // We mainly care about lg breakpoint where grid is 3 cols.
                $isRightEdge = ($index + 1) % 3 === 0;
                $tooltipClass = $isRightEdge ? 'right-0 origin-top-right' : 'left-0 origin-top-left';
                $arrowClass = $isRightEdge ? 'right-2' : 'left-2';
            @endphp

            {{-- 
               FIX: Removed 'overflow-hidden' from here so Tooltip can pop out.
               Added group class for hover effects.
               Added hover:z-20 for Stacking Context fix.
            --}}
            <div class="relative rounded-2xl bg-white p-6 shadow-sm border border-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:z-20 group">
                
                {{-- 
                   FIX: Inner layer for Background Decoration. 
                   This has overflow-hidden to clip the circle, but is absolute/behind content.
                --}}
                <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full {{ $bgClass }} opacity-50 transition-transform group-hover:scale-110"></div>
                </div>

                <div class="relative z-10 flex justify-between items-start">
                    <div class="flex-1"> {{-- Added flex-1 to push icon to right --}}
                        <div class="flex items-start gap-2"> {{-- Changed items-center to items-start --}}
                            <span class="text-sm font-medium text-gray-500">{{ $card['name'] }}</span> {{-- Removed line-clamp-1 --}}
                            
                             {{-- Info Tooltip --}}
                             <div class="relative group/info cursor-help inline-block mt-0.5"> {{-- Added mt-0.5 for optical alignment --}}
                                <svg class="w-4 h-4 text-gray-300 hover:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{-- Tooltip Box: z-50 to float above everything --}}
                                <div class="absolute z-50 invisible w-64 p-3 mt-2 text-xs font-normal text-white bg-gray-900 rounded-lg opacity-0 {{ $tooltipClass }} group-hover/info:visible group-hover/info:opacity-100 transition-all duration-200 shadow-xl">
                                    {{ $card['description'] }}
                                    {{-- Arrow --}}
                                    <div class="absolute -top-1 {{ $arrowClass }} w-2 h-2 bg-gray-900 rotate-45"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 flex flex-wrap items-baseline gap-2">
                             <span class="text-2xl font-bold text-gray-900 leading-tight">
                                {{ $card['unit'] === 'IDR' ? 'Rp' . number_format($card['value'], 0, ',', '.') : ($card['unit'] === '%' ? number_format($card['value'], 1) . '%' : number_format($card['value'])) }}
                            </span>
                            
                            {{-- Status Pill --}}
                            @if($card['status'] === 'blue')
                                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" /></svg>
                                    Strong
                                </span>
                            @elseif($card['status'] === 'green')
                                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    On Track
                                </span>
                            @else
                                <span class="text-xs font-medium text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-4a1 1 0 112 0v-4a1 1 0 11-2 0v4zm1-9a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" /></svg>
                                    Review
                                </span>
                            @endif
                        </div>
                        <div class="mt-1 text-xs text-gray-400">Target: {{ number_format($card['target']) }}</div>
                    </div>

                    {{-- Icon Container --}}
                    <div class="p-3 rounded-xl {{ $bgClass }} {{ $textClass }} shrink-0 ml-4">
                        @if($icon === 'banknotes')
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @elseif($icon === 'arrow-path')
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        @elseif($icon === 'users')
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        @elseif($icon === 'ticket')
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v9.632a2.25 2.25 0 01-.894 1.785 2.25 2.25 0 001.077 4.083h19.134a2.25 2.25 0 001.077-4.083 2.25 2.25 0 01-.894-1.785V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg>
                        @elseif($icon === 'credit-card')
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        @elseif($icon === 'star')
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                        @elseif($icon === 'megaphone')
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.467a23.849 23.849 0 010 3.467m0-3.467a23.849 23.849 0 000 3.467" /></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                        @endif
                    </div>

                </div>

                {{-- Sparkline Chart (Edge-to-Edge) --}}
                <div class="mt-4 h-24 w-[calc(100%+3rem)] -ml-6 -mb-6 relative z-10 overflow-hidden rounded-b-2xl">
                    <canvas id="sparkline-{{ $loop->index }}" 
                            data-chart="{{ json_encode($card['chart_data'] ?? [0,0,0]) }}"
                            data-color="{{ $color }}">
                    </canvas>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 2. Geospatial Market Gap & CLV --}}
    <div wire:loading.remove class="grid grid-cols-1 lg:grid-cols-3 gap-8"
         x-show="loaded"
         x-transition:enter="transition ease-out duration-700 delay-100"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0">
         
        {{-- Matrix Chart --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- CLV Chart --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-start mb-6">
                     <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-gray-900">Matriks Nilai Pelanggan (CLV)</h3>
                             {{-- Info Tooltip --}}
                             <div class="relative group/info cursor-help inline-block">
                                <svg class="w-4 h-4 text-gray-300 hover:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="absolute z-50 invisible w-64 p-3 mt-2 text-xs leading-relaxed text-white bg-gray-900 rounded-lg opacity-0 -right-1/2 group-hover/info:visible group-hover/info:opacity-100 transition-all duration-200 shadow-xl">
                                    Identifikasi segmen pelanggan berdasarkan Frekuensi Pembelian (Sumbu X) dan Total Nilai Uang (Sumbu Y). Pelanggan di kanan atas adalah 'Champions'.
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Segmentasi berdasarkan Frekuensi vs Nominal Transaksi</p>
                     </div>
                </div>
                <div class="h-80 w-full relative">
                    <canvas id="clvBubbleChart"></canvas>
                </div>
            </div>

            {{-- Market Gap Analysis --}}
             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-gray-900">Geospatial Market Gap</h3>
                             {{-- Info Tooltip --}}
                             <div class="relative group/info cursor-help inline-block">
                                <svg class="w-4 h-4 text-gray-300 hover:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="absolute z-50 invisible w-64 p-3 mt-2 text-xs leading-relaxed text-white bg-gray-900 rounded-lg opacity-0 -right-1/2 group-hover/info:visible group-hover/info:opacity-100 transition-all duration-200 shadow-xl">
                                    Market Gap = (Permintaan Service ÷ Jumlah Bengkel) x 100. Skor tinggi menunjukkan peluang ekspansi karena permintaan tinggi namun suplai rendah.
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Top 5 Kota dengan Peluang Ekspansi Tertinggi</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    @forelse($marketGap as $city)
                        <div class="group">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-900">{{ $city['city'] }}</span>
                                <span class="text-indigo-600 font-bold">{{ number_format($city['gap_score'], 1) }}% Gap</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2.5 rounded-full" 
                                     style="width: {{ min($city['gap_score'], 100) }}%"></div>
                            <div class="flex justify-between text-xs text-gray-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span>Demand: {{ $city['demand'] }} reqs</span>
                                <span>Supply: {{ $city['supply'] }} workshops</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 text-sm">
                            Belum ada data geospasial yang cukup untuk analisis gap.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- AI Customer Segmentation (New Card) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-gray-900">Segmentasi Pelanggan (AI-RFM)</h3>
                            <div class="relative group/info cursor-help inline-block">
                                <svg class="w-4 h-4 text-gray-300 hover:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="absolute z-50 invisible w-64 p-3 mt-2 text-xs leading-relaxed text-white bg-gray-900 rounded-lg opacity-0 -right-1/2 group-hover/info:visible group-hover/info:opacity-100 transition-all duration-200 shadow-xl">
                                    Segmentasi otomatis menggunakan model RFM (Recency, Frequency, Monetary). 'Champions' adalah pelanggan terbaik Anda.
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Total {{ $customerSegmentation['total_analyzed'] }} pelanggan dianalisa</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div class="h-64 relative">
                        <canvas id="segmentationChart"></canvas>
                    </div>
                    <div class="space-y-3">
                        @foreach($customerSegmentation['segments'] as $segment => $count)
                            @if($count > 0)
                                <div class="flex justify-between items-center text-sm">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ 
                                            match($segment) {
                                                'Champions' => '#10b981', // emerald
                                                'Loyal Customers' => '#3b82f6', // blue
                                                'Potential Loyalists' => '#8b5cf6', // violet
                                                'New Customers' => '#06b6d4', // cyan
                                                'At Risk' => '#f59e0b', // amber
                                                'Hibernating' => '#64748b', // slate
                                                default => '#d1d5db'
                                            }
                                        }};"></span>
                                        {{ $segment }}
                                    </span>
                                    <span class="font-bold text-gray-700">{{ $count }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                </div>
            </div>

        {{-- Insight Panel --}}
        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex-1">
                 <h3 class="text-lg font-bold text-gray-900 mb-6">Quick Insights</h3>
                 
                 <div class="space-y-6">
                     <div class="relative pl-4 border-l-4 border-indigo-500">
                         <div class="text-xs font-semibold text-indigo-500 uppercase tracking-wide">High Value Customers</div>
                         <div class="text-3xl font-bold text-gray-900 mt-1">{{ $clvAnalysis['summary']['high_value_count'] }}</div>
                         <div class="text-sm text-gray-500 mt-1">Pelanggan dengan LTV > Rp 10 Juta</div>
                     </div>
                     
                     <div class="relative pl-4 border-l-4 border-emerald-500">
                         <div class="text-xs font-semibold text-emerald-500 uppercase tracking-wide">Rata-rata LTV</div>
                         <div class="text-3xl font-bold text-gray-900 mt-1">Rp {{ number_format($clvAnalysis['summary']['avg_ltv'], 0, ',', '.') }}</div>
                         <div class="text-sm text-gray-500 mt-1">Pendapatan per pelanggan berbayar</div>
                     </div>
                 </div>
            </div>

            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h4 class="font-bold text-lg mb-2">Rekomendasi AI</h4>
                    <p class="text-indigo-100 text-sm leading-relaxed">
                        Fokus retensi pada {{ $clvAnalysis['summary']['high_value_count'] }} pelanggan bernilai tinggi. Pertimbangkan program loyalitas eksklusif untuk meningkatkan frekuensi transaksi mereka.
                    </p>
                </div>
                {{-- Decorative Circle --}}
                <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
            </div>
        </div>
    </div>

    {{-- 3. Business Outlook (Platform Intelligence) --}}
    <div wire:loading.remove 
         class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-8"
         x-show="loaded"
         x-transition:enter="transition ease-out duration-700 delay-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0">
         
         <div class="flex items-center gap-3 mb-8">
            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Platform Business Outlook (SaaS)</h2>
                <p class="text-sm text-gray-500">Prediksi kesehatan bisnis BBI Hub & Peluang Pertumbuhan</p>
            </div>
         </div>

         <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
             {{-- MRR Forecast --}}
             <div class="p-6 bg-gray-50 rounded-xl border border-gray-100">
                 <h3 class="font-bold text-gray-900 mb-1">Prediksi Pendapatan (MRR)</h3>
                 <p class="text-xs text-gray-500 mb-4">Proyeksi bulan depan berbasis Linear Regression</p>
                 
                 <div class="flex items-baseline gap-2 mb-4">
                     <span class="text-3xl font-bold text-gray-900">Rp {{ number_format($platformOutlook['mrr_forecast']['prediction'], 0, ',', '.') }}</span>
                     @if($platformOutlook['mrr_forecast']['growth_rate'] > 0)
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">+{{ number_format($platformOutlook['mrr_forecast']['growth_rate'], 1) }}%</span>
                     @else
                        <span class="text-xs font-bold text-rose-600 bg-rose-100 px-2 py-0.5 rounded-full">{{ number_format($platformOutlook['mrr_forecast']['growth_rate'], 1) }}%</span>
                     @endif
                 </div>
                 <div class="h-32">
                     <canvas id="mrrForecastChart"></canvas>
                 </div>
             </div>

             {{-- Churn Risk --}}
             <div class="p-6 bg-rose-50 rounded-xl border border-rose-100">
                 <h3 class="font-bold text-rose-900 mb-1">Resiko Churn (High Risk)</h3>
                 <p class="text-xs text-rose-700 mb-4">Bengkel dengan penurunan aktivitas ekstrim (>50%)</p>
                 
                 <div class="space-y-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                     @forelse($platformOutlook['churn_candidates'] as $risk)
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-rose-100 flex justify-between items-center">
                            <div>
                                <div class="font-bold text-gray-900 text-sm">{{ $risk['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $risk['owner'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-bold text-rose-600">Drop {{ $risk['drop_rate'] }}</div>
                                <div class="text-[10px] text-gray-400">{{ $risk['prev_vol'] }} -> {{ $risk['current_vol'] }} trx</div>
                            </div>
                        </div>
                     @empty
                        <div class="text-center py-4 text-rose-400 text-sm">Tidak ada bengkel beresiko saat ini.</div>
                     @endforelse
                 </div>
             </div>

             {{-- Upsell Opportunities --}}
             <div class="p-6 bg-emerald-50 rounded-xl border border-emerald-100">
                 <h3 class="font-bold text-emerald-900 mb-1">Peluang Upsell (Hot Leads)</h3>
                 <p class="text-xs text-emerald-700 mb-4">Pengguna Free dengan volume transaksi tinggi</p>
                 
                 <div class="space-y-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                     @forelse($platformOutlook['upsell_candidates'] as $lead)
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-emerald-100 flex justify-between items-center">
                            <div>
                                <div class="font-bold text-gray-900 text-sm">{{ $lead['workshop'] }}</div>
                                <div class="text-xs text-gray-500">{{ $lead['owner'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-bold text-indigo-600">{{ $lead['volume'] }} Trx/mo</div>
                                <button class="mt-1 text-[10px] bg-emerald-600 text-white px-2 py-0.5 rounded hover:bg-emerald-700">Tawarkan Premium</button>
                            </div>
                        </div>
                     @empty
                        <div class="text-center py-4 text-emerald-500 text-sm">Belum ada kandidat upsell potensial.</div>
                     @endforelse
                 </div>
             </div>
         </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:navigated', initCharts);
    document.addEventListener('DOMContentLoaded', initCharts);
    window.addEventListener('refresh-charts', initCharts); // Listen for Livewire event

    function initCharts() {
        initClvChart();
        initSparklines();
        initSegmentationChart();
        initMrrForecastChart();
    }
    
    // ... existing charts ...

    function initMrrForecastChart() {
        const ctx = document.getElementById('mrrForecastChart');
        if (!ctx) return;
        if (window.mrrChart) window.mrrChart.destroy();

        const rawData = @json($platformOutlook['mrr_forecast']['history'] ?? []);
        const labels = rawData.map(d => d.label);
        const values = rawData.map(d => d.y);
        
        // Add Prediction Point
        const predictedVal = @json($platformOutlook['mrr_forecast']['prediction'] ?? 0);
        labels.push('Next Month (Pred)');
        // Pad values with null for historical line, add prediction at end
        // Actually for simplicity, just append to array
        const historicalData = [...values, null]; 
        const predictedData = Array(values.length).fill(null);
        predictedData.push(values[values.length-1]); // Connect lines
        predictedData.push(predictedVal);

        window.mrrChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'History',
                        data: historicalData,
                        borderColor: '#4f46e5', // Indigo
                        backgroundColor: '#4f46e5',
                        tension: 0.3
                    },
                    {
                        label: 'AI Forecast',
                        data: predictedData,
                        borderColor: '#9333ea', // Purple
                        borderDash: [5, 5],
                        backgroundColor: '#9333ea',
                        pointStyle: 'star',
                        pointRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }, // Compact
                scales: {
                    x: { display: false },
                    y: { 
                        display: true, 
                        ticks: { 
                            callback: (val) => (val/1000000).toFixed(0) + 'jt' // Abbreviate Millions
                        } 
                    }
                }
            }
        });
    }
    
    // ... existing functions ...


    function initSegmentationChart() {
        const ctx = document.getElementById('segmentationChart');
        if (!ctx) return;
        
        if (window.segmentationChart) window.segmentationChart.destroy();
        
        const rawData = @json($customerSegmentation['segments']);
        const labels = Object.keys(rawData);
        const values = Object.values(rawData);
        
        const colors = {
            'Champions': '#10b981', 
            'Loyal Customers': '#3b82f6', 
            'Potential Loyalists': '#8b5cf6', 
            'New Customers': '#06b6d4', 
            'At Risk': '#f59e0b', 
            'Hibernating': '#64748b', 
            'Others': '#d1d5db'
        };
        const bgColors = labels.map(label => colors[label] || '#9ca3af');

        window.segmentationChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: bgColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                         backgroundColor: 'rgba(17, 24, 39, 0.9)',
                         padding: 12,
                         cornerRadius: 8,
                         callbacks: {
                             label: function(context) {
                                 const label = context.label || '';
                                 const val = context.raw;
                                 const total = context.chart._metasets[context.datasetIndex].total;
                                 const percentage = ((val / total) * 100).toFixed(1) + '%';
                                 return `${label}: ${val} (${percentage})`;
                             }
                         }
                    }
                }
            }
        });
    }

    function initSparklines() {
        if (window.sparklineInstances) {
            window.sparklineInstances.forEach(chart => chart.destroy());
        }
        window.sparklineInstances = [];

        // Select all canvases matching pattern
        const canvases = document.querySelectorAll('canvas[id^="sparkline-"]');
        
        canvases.forEach((canvas) => {
            const chartData = JSON.parse(canvas.dataset.chart);
            const colorName = canvas.dataset.color || 'gray';

            // Simple map for Tailwind Colors to Hex
            const colors = {
                'emerald': '#10b981', 'blue': '#2563eb', 'cyan': '#06b6d4', 
                'violet': '#8b5cf6', 'amber': '#f59e0b', 'rose': '#f43f5e', 'gray': '#6b7280'
            };
            const hex = colors[colorName] || '#6b7280';

            const chart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: chartData.map((_, i) => i),
                    datasets: [{
                        data: chartData,
                        borderColor: hex,
                        backgroundColor: hex + '1A', // 10% opacity
                        borderWidth: 2,
                        pointRadius: 0,
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
                        y: { display: false, min: Math.min(...chartData) * 0.9 }
                    },
                    layout: { padding: 0 }
                }
            });
            window.sparklineInstances.push(chart);
        });
    }

    function initClvChart() {
        const ctx = document.getElementById('clvBubbleChart');
        if (!ctx) return;
        
        if (window.clvChart) window.clvChart.destroy();

        const rawData = @json($clvAnalysis['scatter']);
        
        const data = {
            datasets: [{
                label: 'Pelanggan',
                data: rawData,
                backgroundColor: 'rgba(99, 102, 241, 0.6)', 
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1,
                hoverBackgroundColor: 'rgba(244, 63, 94, 0.8)',
                hoverBorderColor: 'rgba(244, 63, 94, 1)'
            }]
        };

        window.clvChart = new Chart(ctx, {
            type: 'bubble',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13 },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function(context) {
                                const v = context.raw;
                                return `Freq: ${v.x} | Value: Rp ${new Intl.NumberFormat('id-ID').format(v.y)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Frekuensi Pembelian', font: { weight: 'bold' } },
                        beginAtZero: true,
                        grid: { borderDash: [2, 2] }
                    },
                    y: {
                        title: { display: true, text: 'Total Nilai (IDR)', font: { weight: 'bold' } },
                        beginAtZero: true,
                        grid: { borderDash: [2, 2] }
                    }
                }
            }
        });
    }
</script>
@endpush