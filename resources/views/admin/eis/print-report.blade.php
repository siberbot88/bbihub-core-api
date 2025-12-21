<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Eksekutif EIS - {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                -webkit-print-color-adjust: exact;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .sheet {
                margin: 0;
                box-shadow: none;
                width: 210mm;
                height: 297mm; /* Crucial for finding A4 */
                overflow: hidden;
                page-break-after: always;
                padding: 15mm; /* Internal padding */
            }
            
            /* Hide URL printing */
            a[href]:after { content: none !important; }
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            margin: 0;
        }

        .sheet {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="text-gray-800">

    {{-- Controls --}}
    <div class="no-print fixed top-4 right-4 flex gap-2">
        <button onclick="window.print()"
            class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak PDF
        </button>
        <button onclick="window.close()"
            class="bg-gray-200 text-gray-700 px-4 py-2 rounded shadow hover:bg-gray-300 font-medium">
            Tutup
        </button>
    </div>

    {{-- Page 1: Summary & Scorecard --}}
    <div class="sheet">
        {{-- Header --}}
        <div class="flex justify-between items-center border-b-2 border-gray-900 pb-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Laporan Eksekutif</h1>
                <p class="text-gray-500 mt-1">Decision Intelligence System</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-blue-600">
                    {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
                </div>
                <div class="text-sm text-gray-400">Generated: {{ now()->format('d M Y H:i') }}</div>
            </div>
        </div>

        {{-- Executive Summary (AI Generated) --}}
        <div class="bg-blue-50 border-l-4 border-blue-600 p-6 mb-8 rounded-r-lg">
            <h3 class="text-lg font-bold text-blue-900 mb-2">Ringkasan Eksekutif</h3>
            <p class="text-blue-800 leading-relaxed text-justify">
                {!! nl2br(e($analysis['summary'])) !!}
            </p>
        </div>

        {{-- KPI Scorecard Table --}}
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Kartu Skor Kinerja (KPI Scorecard)</h3>
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Metrik</th>
                        <th class="px-4 py-3">Aktual</th>
                        <th class="px-4 py-3">Target</th>
                        <th class="px-4 py-3">Pencapaian</th>
                        <th class="px-4 py-3 rounded-r-lg text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($scorecard as $item)
                        @php
                            $ratio = $item['target'] > 0 ? ($item['value'] / $item['target']) * 100 : 0;
                            $statusColor = match ($item['status']) {
                                'blue' => 'text-blue-600 bg-blue-50',
                                'green' => 'text-emerald-600 bg-emerald-50',
                                'yellow' => 'text-amber-600 bg-amber-50',
                                'red' => 'text-rose-600 bg-rose-50',
                                default => 'text-gray-600'
                            };
                            $statusLabel = match ($item['status']) {
                                'blue' => 'Excellent',
                                'green' => 'On Track',
                                'yellow' => 'Warning',
                                'red' => 'Critical',
                                default => '-'
                            };
                        @endphp
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $item['name'] }}</td>
                            <td class="px-4 py-3 font-bold">
                                {{ $item['unit'] == 'IDR' ? 'Rp ' . number_format($item['value'], 0, ',', '.') : number_format($item['value']) . ($item['unit'] == '%' ? '%' : '') }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $item['unit'] == 'IDR' ? 'Rp ' . number_format($item['target'], 0, ',', '.') : number_format($item['target']) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="w-24 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gray-800 h-2 rounded-full" style="width: {{ min($ratio, 100) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 mt-1 block">{{ number_format($ratio, 1) }}%</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Revenue Analysis Text --}}
        <div class="mb-4">
            <h4 class="font-bold text-gray-900 mb-2">Analisis Pendapatan</h4>
            <p class="text-gray-700 text-sm leading-relaxed text-justify border-l-2 border-gray-300 pl-4">
                {{ $analysis['revenue'] }}
            </p>
        </div>
    </div>

    {{-- Page 2: Charts & Deep Dive --}}
    <div class="sheet page-break">
        <div class="flex justify-between items-center border-b pb-4 mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Analisis Mendalam</h2>
            <div class="text-sm text-gray-400">Halaman 2</div>
        </div>

        {{-- 1. Segmentation Chart --}}
        <div class="mb-8 grid grid-cols-2 gap-8">
            <div>
                <h3 class="font-bold text-gray-900 mb-4">Segmentasi Pelanggan (AI-RFM)</h3>
                <div class="h-64 relative">
                    <canvas id="segmentChart"></canvas>
                </div>
            </div>
            <div class="flex flex-col justify-center">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-bold text-sm text-gray-900 mb-2">Insight Pelanggan</h4>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        {{ $analysis['customer'] }}
                    </p>
                </div>
                <div class="mt-4">
                    <h4 class="font-bold text-sm text-gray-900 mb-2">Distribusi:</h4>
                    <ul class="text-sm space-y-1">
                        @foreach($segmentation['segments'] as $seg => $cnt)
                            @if($cnt > 0)
                                <li class="flex justify-between">
                                    <span class="text-gray-600">{{ $seg }}</span>
                                    <span class="font-bold">{{ $cnt }} Users</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- 2. Recommendations --}}
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 mb-8">
            <h3 class="text-lg font-bold text-indigo-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Rekomendasi Strategis (AI)
            </h3>
            <div class="text-indigo-800 text-sm leading-loose whitespace-pre-line font-medium">
                {{ $analysis['recommendation'] }}
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-400 mt-12 border-t pt-4">
            Dokumen ini digenerate secara otomatis oleh Business Intelligence System.
            <br>Rahasia Perusahaan. Jangan disebarluaskan tanpa izin.
        </div>
    </div>

    <script>
        // Init Segmentation Chart
        const ctx = document.getElementById('segmentChart');
        const rawData = @json($segmentation['segments']);

        const colors = {
            'Champions': '#10b981',
            'Loyal Customers': '#3b82f6',
            'Potential Loyalists': '#8b5cf6',
            'New Customers': '#06b6d4',
            'At Risk': '#f59e0b',
            'Hibernating': '#64748b',
            'Others': '#d1d5db'
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(rawData),
                datasets: [{
                    data: Object.values(rawData),
                    backgroundColor: Object.keys(rawData).map(k => colors[k]),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                }
            }
        });

        // Auto print prompt
        // setTimeout(() => window.print(), 1000);
    </script>
</body>

</html>