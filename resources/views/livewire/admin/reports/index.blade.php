<div id="feedback-page" class="mx-auto max-w-screen-2xl p-4 md:p-6 lg:p-8 space-y-6">

    {{-- Top search --}}
    <div class="flex items-center gap-3">
        <div class="relative w-full">
            <input
                type="text"
                placeholder="Cari Pengguna…"
                class="h-11 w-full rounded-xl border border-neutral-200 bg-white ps-11 pe-4 text-sm placeholder:text-neutral-400 focus:border-red-400 focus:ring-red-400"
            />
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-neutral-400" viewBox="0 0 24 24" fill="none">
                <path d="M21 21l-4.3-4.3M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="flex items-center gap-2">
            <button class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-neutral-200 bg-white hover:bg-neutral-50">
                <svg class="h-5 w-5 text-neutral-600" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
            <button class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-neutral-200 bg-white hover:bg-neutral-50">
                <svg class="h-5 w-5 text-neutral-600" viewBox="0 0 24 24" fill="none">
                    <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-neutral-200 bg-white hover:bg-neutral-50">
                <svg class="h-5 w-5 text-neutral-600" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Header panel "Feedback/Laporan" --}}
    <div class="rounded-2xl border border-neutral-200 bg-white p-5 md:p-6">
        <div class="flex items-start gap-3">
            <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-100 text-red-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <div class="font-semibold">Feedback/Laporan</div>
                <div class="text-sm text-neutral-500">Monitor dan kelola seluruh booking dan transaksi</div>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
        @php
            $stats = [
                ['title'=>'Total laporan masuk','value'=>20,'trend'=>'+5%','icon'=>'inbox'],
                ['title'=>'Laporan masuk hari ini','value'=>12,'trend'=>'+2%','icon'=>'calendar'],
                ['title'=>'Diproses','value'=>10,'trend'=>'+5%','icon'=>'progress'],
                ['title'=>'Selesai','value'=>50,'trend'=>'+5%','icon'=>'check'],
                ['title'=>'','value'=>null,'trend'=>null,'icon'=>null], // spacer for layout identical to figma (optional)
            ];
        @endphp
        @foreach ($stats as $s)
            @if($s['value']!==null)
            <div class="rounded-2xl border border-neutral-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium text-neutral-600">{{ $s['title'] }}</div>
                    <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-neutral-100">
                        @switch($s['icon'])
                            @case('inbox')
                                <svg class="h-5 w-5 text-neutral-600" viewBox="0 0 24 24" fill="none"><path d="M3 12h4l2 3h6l2-3h4M7 12V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            @break
                            @case('calendar')
                                <svg class="h-5 w-5 text-neutral-600" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M16 3v4M8 3v4M3 11h18" stroke="currentColor" stroke-width="2"/></svg>
                            @break
                            @case('progress')
                                <svg class="h-5 w-5 text-neutral-600" viewBox="0 0 24 24" fill="none"><path d="M4 19h16M6 17V7m6 10V5m6 12v-8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            @break
                            @case('check')
                                <svg class="h-5 w-5 text-neutral-600" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            @break
                        @endswitch
                    </div>
                </div>
                <div class="mt-3 text-2xl font-bold text-neutral-900">{{ $s['value'] }}</div>
                <div class="mt-1 text-xs text-neutral-400">update {{ $s['trend'] }}</div>
            </div>
            @else
            <div class="hidden xl:block"></div>
            @endif
        @endforeach
    </div>

    {{-- Filter bar --}}
    <div class="rounded-2xl border border-neutral-200 bg-white p-4 md:p-5">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="sm:col-span-2">
                <div class="relative">
                    <input type="text" placeholder="Cari Laporan…" class="h-10 w-full rounded-xl border border-neutral-200 bg-white ps-10 pe-4 text-sm placeholder:text-neutral-400 focus:border-red-400 focus:ring-red-400">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-neutral-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-4.3-4.3M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
            </div>
            <select class="h-10 rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-700 focus:border-red-400 focus:ring-red-400">
                <option>Semua Status</option>
                <option>Baru</option>
                <option>Diproses</option>
                <option>Diterima</option>
                <option>Selesai</option>
            </select>
            <select class="h-10 rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-700 focus:border-red-400 focus:ring-red-400">
                <option>Jenis Laporan</option>
                <option>Bug</option>
                <option>Keluhan</option>
                <option>Saran</option>
                <option>Ulasan</option>
            </select>
            <div>
                <input type="date" class="h-10 w-full rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-700 focus:border-red-400 focus:ring-red-400">
            </div>
            <div class="flex justify-end">
                <button class="inline-flex h-10 items-center gap-2 rounded-xl border border-neutral-200 bg-white px-3 text-sm hover:bg-neutral-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M4 4v6h6M20 20v-6h-6M20 4h-6v6M4 20h6v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>Refresh</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border border-neutral-200 bg-white">
        <div class="flex items-center justify-between p-4 md:p-5">
            <div class="font-semibold">Daftar booking &amp; transaksi</div>
            <div class="text-sm text-neutral-500">Total: <span class="font-medium text-neutral-700">150 Laporan</span></div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full border-t border-neutral-100 text-sm">
                <thead class="bg-neutral-50 text-neutral-600">
                    <tr>
                        <th class="p-4 text-left font-medium">Pengirim</th>
                        <th class="p-4 text-left font-medium">Jenis Laporan</th>
                        <th class="p-4 text-left font-medium">Deskripsi</th>
                        <th class="p-4 text-left font-medium">TANGGAL</th>
                        <th class="p-4 text-left font-medium">STATUS</th>
                        <th class="p-4 text-left font-medium">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-neutral-800">
                    @php
                        $rows = [
                            ['pengirim'=>'Diana Puja','jenis'=>'Bug','desk'=>'Tidak bisa booking','tgl'=>'25 Okt 2025','status'=>['Diterima','blue'],'aksi'=>'Detail'],
                            ['pengirim'=>'Kadian Ahmad','jenis'=>'Keluhan','desk'=>'Pembayaran tertunda','tgl'=>'27 Okt 2025','status'=>['Selesai','green'],'aksi'=>'Detail'],
                            ['pengirim'=>'Kadian Ahmad','jenis'=>'Saran','desk'=>'Tambahkan metode Qris','tgl'=>'31 Okt 2025','status'=>['Baru','yellow'],'aksi'=>'Detail'],
                            ['pengirim'=>'Kadian Ahmad','jenis'=>'Ulasan','desk'=>'Menangannya cepat','tgl'=>'28 Okt 2025','status'=>['Proses','purple'],'aksi'=>'Detail'],
                            ['pengirim'=>'Kadian Ahmad','jenis'=>'Bug','desk'=>'Status tidak berubah','tgl'=>'1 Nov 2025','status'=>['Diterima','blue'],'aksi'=>'Detail'],
                        ];
                        $statusColors = [
                            'blue'=>'bg-blue-100 text-blue-700',
                            'green'=>'bg-green-100 text-green-700',
                            'yellow'=>'bg-amber-100 text-amber-700',
                            'purple'=>'bg-purple-100 text-purple-700',
                        ];
                    @endphp

                    @foreach($rows as $r)
                    <tr class="hover:bg-neutral-50/60">
                        <td class="p-4">{{ $r['pengirim'] }}</td>
                        <td class="p-4 text-neutral-600">{{ $r['jenis'] }}</td>
                        <td class="p-4 text-neutral-600">{{ $r['desk'] }}</td>
                        <td class="p-4">{{ $r['tgl'] }}</td>
                        <td class="p-4">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColors[$r['status'][1]] }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $r['status'][0] }}
                            </span>
                        </td>
                        <td class="p-4">
                            <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Detail</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-end gap-2 p-4 md:p-5">
            <button class="rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-sm hover:bg-neutral-50">Sebelum</button>
            <button class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white">1</button>
            <button class="rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-sm hover:bg-neutral-50">2</button>
            <button class="rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-sm hover:bg-neutral-50">3</button>
            <button class="rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-sm hover:bg-neutral-50">Selanjutnya</button>
        </div>
    </div>

    {{-- Footer logo block --}}
    <div class="rounded-2xl border border-neutral-200 bg-white p-6">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-red-100"></div>
            <div>
                <div class="text-lg font-semibold">BBI HUB</div>
                <div class="text-sm text-neutral-500">Plus</div>
            </div>
        </div>
    </div>
</div>
