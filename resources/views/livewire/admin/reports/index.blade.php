<div id="feedback-page" class="mx-auto max-w-screen-2xl p-4 md:p-6 lg:p-8 space-y-6">

    {{-- Top search --}}
    <div class="flex items-center gap-3">
        <div class="relative w-full">
            <input
                type="text"
                placeholder="Cari Laporan…"
                wire:model.live.debounce.500ms="q"
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
                <div class="text-sm text-neutral-500">Monitor dan kelola seluruh laporan dari pengguna</div>
            </div>
        </div>
    </div>

    {{-- Summary Cards Laporan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            use App\Models\Report;
            use Carbon\Carbon;

            $today = Carbon::today();

            $total      = Report::count();
            $todayCount = Report::whereDate('created_at', $today)->count();
            $processing = Report::where('status', 'diproses')->count();
            $done       = Report::where('status', 'selesai')->count();

            $stats = [
                [
                    'title' => 'Total laporan masuk',
                    'value' => $total,
                    'trend' => '+0%',
                    'icon'  => 'mail',
                    'color' => 'blue',
                ],
                [
                    'title' => 'Laporan masuk hari ini',
                    'value' => $todayCount,
                    'trend' => '+0%',
                    'icon'  => 'calendar',
                    'color' => 'yellow',
                ],
                [
                    'title' => 'Diproses',
                    'value' => $processing,
                    'trend' => '+0%',
                    'icon'  => 'chart',
                    'color' => 'purple',
                ],
                [
                    'title' => 'Selesai',
                    'value' => $done,
                    'trend' => '+0%',
                    'icon'  => 'check',
                    'color' => 'green',
                ],
            ];
        @endphp

        @php
            $iconColors = [
                'blue'   => 'bg-blue-50 text-blue-600',
                'yellow' => 'bg-yellow-50 text-yellow-600',
                'purple' => 'bg-purple-50 text-purple-600',
                'green'  => 'bg-emerald-50 text-emerald-600',
            ];
        @endphp

        @foreach ($stats as $card)
        <div class="group rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
            <div class="flex items-center justify-between">
            <div class="flex-1">
                <div class="text-sm text-gray-500">{{ $card['title'] }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($card['value']) }}</div>
                <div class="mt-1 text-xs text-emerald-600">update {{ $card['trend'] }}</div>
            </div>

            <div class="h-12 w-12 rounded-xl {{ $iconColors[$card['color']] ?? 'bg-gray-50 text-gray-600' }} flex items-center justify-center transition-transform group-hover:scale-110">
                @if($card['icon'] === 'mail')
                {{-- envelope --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5V6.75m18 0A1.5 1.5 0 0019.5 5.25h-15A1.5 1.5 0 003 6.75m18 0v.243a1.5 1.5 0 01-.553 1.154l-7.5 6.25a1.5 1.5 0 01-1.894 0l-7.5-6.25A1.5 1.5 0 013 6.993V6.75" />
                </svg>
                @elseif($card['icon'] === 'calendar')
                {{-- calendar --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 9h18m-1.5 12H4.5a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5h15a1.5 1.5 0 011.5 1.5V19.5a1.5 1.5 0 01-1.5 1.5z" />
                </svg>
                @elseif($card['icon'] === 'chart')
                {{-- chart bar --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18m0 0h18M9 17V9m4 8V5m4 12v-6" />
                </svg>
                @elseif($card['icon'] === 'check')
                {{-- check circle --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                @endif
            </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filter bar --}}
    <div class="rounded-2xl border border-neutral-200 bg-white p-4 md:p-5">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="sm:col-span-2">
                <div class="relative">
                    <input
                        type="text"
                        placeholder="Cari Laporan…"
                        wire:model.live.debounce.500ms="q"
                        class="h-10 w-full rounded-xl border border-neutral-200 bg-white ps-10 pe-4 text-sm placeholder:text-neutral-400 focus:border-red-400 focus:ring-red-400">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-neutral-400" viewBox="0 0 24 24" fill="none">
                        <path d="M21 21l-4.3-4.3M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>

            {{-- Status --}}
            <select
                wire:model.live="status"
                class="h-10 rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-700 focus:border-red-400 focus:ring-red-400">
                <option value="all">Semua Status</option>
                <option value="baru">Baru</option>
                <option value="diproses">Diproses</option>
                <option value="diterima">Diterima</option>
                <option value="selesai">Selesai</option>
            </select>

            {{-- Jenis laporan --}}
            <select
                wire:model.live="type"
                class="h-10 rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-700 focus:border-red-400 focus:ring-red-400">
                <option value="all">Jenis Laporan</option>
                <option value="bug">Bug</option>
                <option value="keluhan">Keluhan</option>
                <option value="saran">Saran</option>
                <option value="ulasan">Ulasan</option>
            </select>

            {{-- Tanggal --}}
            <div>
                <input
                    type="date"
                    wire:model.live="date"
                    class="h-10 w-full rounded-xl border border-neutral-200 bg-white px-3 text-sm text-neutral-700 focus:border-red-400 focus:ring-red-400">
            </div>

            <div class="flex justify-end">
                <button
                    type="button"
                    wire:click="$set('q',''); $set('status','all'); $set('type','all'); $set('date', null)"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-neutral-200 bg-white px-3 text-sm hover:bg-neutral-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4v6h6M20 20v-6h-6M20 4h-6v6M4 20h6v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border border-neutral-200 bg-white">
        <div class="flex items-center justify-between p-4 md:p-5">
            <div class="font-semibold">Laporan</div>
            <div class="text-sm text-neutral-500">
                Total:
                <span class="font-medium text-neutral-700">{{ number_format($rows->total()) }} Laporan</span>
            </div>
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
                        $statusColors = [
                            'baru'     => 'bg-amber-100 text-amber-700',
                            'diproses' => 'bg-purple-100 text-purple-700',
                            'diterima' => 'bg-blue-100 text-blue-700',
                            'selesai'  => 'bg-green-100 text-green-700',
                        ];
                    @endphp

                    @forelse($rows as $r)
                        <tr class="hover:bg-neutral-50/60">
                            <td class="p-4">
                                {{ $r->user?->name ?? '—' }}
                            </td>
                            <td class="p-4 text-neutral-600">
                                {{ ucfirst($r->report_type ?? '-') }}
                            </td>
                            <td class="p-4 text-neutral-600">
                                {{ \Illuminate\Support\Str::limit($r->report_data, 60) }}
                            </td>
                            <td class="p-4">
                                {{ $r->created_at?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="p-4">
                                @php
                                    $status = $r->status ?? 'baru';
                                @endphp
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColors[$status] ?? 'bg-neutral-100 text-neutral-600' }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <button class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-neutral-500">
                                Belum ada laporan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between gap-2 p-4 md:p-5">
            <div class="text-xs text-neutral-500">
                Menampilkan
                <span class="font-medium">{{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }}</span>
                dari
                <span class="font-medium">{{ $rows->total() }}</span> laporan
            </div>
            <div>
                {{ $rows->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>
