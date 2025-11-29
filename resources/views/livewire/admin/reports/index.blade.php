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

   {{-- Stat cards (Heroicons) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        @php
            $icons = [
                'Total laporan masuk' => [
                    'bg' => 'bg-blue-50',
                    'color' => 'text-blue-600',
                    'svg' => '
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-4-4h-1m0 6h-4m4 0v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2h5m4 0H7m4 0v-2a4 4 0 00-4-4m8-6a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    ',
                ],
                'Laporan masuk hari ini' => [
                    'bg' => 'bg-yellow-50',
                    'color' => 'text-yellow-600',
                    'svg' => '
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6.75 3v2.25M17.25 3v2.25M3 9h18M4.5 19.5h15a2.25 2.25 0 002.25-2.25V9.75A2.25 2.25 0 0019.5 7.5h-15A2.25 2.25 0 002.25 9.75v7.5A2.25 2.25 0 004.5 19.5zM9 13.5h.008v.008H9v-.008zM12 13.5h.008v.008H12v-.008zM15 13.5h.008v.008H15v-.008z"/>
                        </svg>
                    ',
                ],
                'Diproses' => [
                    'bg' => 'bg-purple-50',
                    'color' => 'text-purple-600',
                    'svg' => '
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    ',
                ],
                'Selesai' => [
                    'bg' => 'bg-green-50',
                    'color' => 'text-green-600',
                    'svg' => '
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12.75l2.25 2.25L15 10.5m-3-7.125A8.625 8.625 0 1018.625 12 8.625 8.625 0 0012 3.375z"/>
                        </svg>
                    ',
                ],
            ];
        @endphp

        @foreach ($this->cards as $card)
            <div class="rounded-2xl border border-neutral-200 bg-white p-5 relative overflow-hidden">
                {{-- Persentase (dummy, bisa diganti dari DB) --}}
                <div class="absolute right-4 top-4 text-xs font-semibold text-green-500">
                    +100%
                </div>

                {{-- Icon --}}
                <div class="h-12 w-12 flex items-center justify-center rounded-xl {{ $icons[$card['label']]['bg'] ?? 'bg-neutral-50' }}">
                    <div class="{{ $icons[$card['label']]['color'] ?? 'text-neutral-600' }}">
                        {!! $icons[$card['label']]['svg'] ?? '' !!}
                    </div>
                </div>

                {{-- Angka --}}
                <div class="mt-4 text-3xl font-bold text-neutral-900">
                    {{ $card['value'] }}
                </div>

                {{-- Label --}}
                <div class="text-sm text-neutral-600">
                    {{ $card['label'] }}
                </div>
            </div>
        @endforeach
    </div>


    {{-- Filter bar --}}
    <div class="rounded-2xl border border-neutral-200 bg-white p-4 md:p-5">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="sm:col-span-2">
                <div class="relative">
                    <input type="text" placeholder="Cari Laporan…" class="h-10 w-full rounded-xl border border-neutral-200 bg-white ps-10 pe-4 text-sm placeholder:text-neutral-400 focus:border-red-400 focus:ring-red-400">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-neutral-400" viewBox="0 0 24 24" fill="none">
                        <path d="M21 21l-4.3-4.3M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
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
                <span class="font-medium text-neutral-700">
                    {{ $reports->total() }} Laporan
                </span>
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
                    @forelse ($reports as $report)
                        @php
                            $status = strtolower($report->status ?? '');
                            $badgeClass = match ($status) {
                                'baru'      => 'bg-amber-100 text-amber-700',
                                'diproses'  => 'bg-purple-100 text-purple-700',
                                'diterima'  => 'bg-blue-100 text-blue-700',
                                'selesai'   => 'bg-green-100 text-green-700',
                                default     => 'bg-neutral-100 text-neutral-700',
                            };
                        @endphp

                        <tr class="hover:bg-neutral-50/60">
                            <td class="p-4">
                                {{ $report->user->name ?? '-' }}
                            </td>
                            <td class="p-4 text-neutral-600">
                                {{ $report->type ?? '-' }} {{-- kolom jenis laporan --}}
                            </td>
                            <td class="p-4 text-neutral-600">
                                {{ $report->description ?? '-' }} {{-- kolom deskripsi --}}
                            </td>
                            <td class="p-4">
                                {{ optional($report->created_at)->translatedFormat('d M Y') }}
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {{ $badgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ ucfirst($status) ?: '-' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <button
                                    wire:click="showDetail('{{ $report->id }}')"
                                    class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-sm text-neutral-500">
                                Belum ada laporan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-end p-4 md:p-5">
            {{ $reports->links() }}
        </div>
    </div>
</div>
