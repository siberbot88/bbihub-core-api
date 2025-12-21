{{-- Detail Modal --}}
@if($showDetailModal && $selectedReport)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showDetailModal') }" x-show="show" x-cloak>
        <div class="flex min-h-screen items-center justify-center p-4">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 transition-opacity" @click="$wire.closeDetailModal()"></div>

            {{-- Modal --}}
            <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-xl">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-neutral-200 p-6">
                    <h3 class="text-xl font-bold text-neutral-800">Detail Laporan</h3>
                    <button @click="$wire.closeDetailModal()" class="rounded-lg p-1 hover:bg-neutral-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content --}}
                <div class="p-6 space-y-4">
                    {{-- Pengirim --}}
                    <div>
                        <label class="text-sm font-medium text-neutral-600">Pengirim</label>
                        <p class="mt-1 text-neutral-800">{{ $selectedReport->workshop?->owner?->name ?? '-' }}</p>
                        <p class="text-sm text-neutral-500">{{ $selectedReport->workshop?->name ?? '-' }}</p>
                    </div>

                    {{-- Jenis & Status --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-neutral-600">Jenis Laporan</label>
                            <p class="mt-1 text-neutral-800">{{ ucfirst($selectedReport->report_type ?? '-') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-neutral-600">Status</label>
                            <div class="mt-1">
                                @php
                                    $statusColors = [
                                        'baru' => 'bg-amber-100 text-amber-700',
                                        'diproses' => 'bg-purple-100 text-purple-700',
                                        'selesai' => 'bg-green-100 text-green-700',
                                    ];
                                    $status = $selectedReport->status ?? 'baru';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColors[$status] ?? 'bg-neutral-100 text-neutral-600' }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ ucfirst($status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label class="text-sm font-medium text-neutral-600">Tanggal Laporan</label>
                        <p class="mt-1 text-neutral-800">{{ $selectedReport->created_at?->format('d F Y, H:i') ?? '-' }}</p>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="text-sm font-medium text-neutral-600">Deskripsi</label>
                        <p class="mt-1 text-neutral-800 whitespace-pre-line">{{ $selectedReport->report_data ?? '-' }}</p>
                    </div>

                    {{-- Foto (jika ada) --}}
                    @if($selectedReport->photo)
                        <div>
                            <label class="text-sm font-medium text-neutral-600">Foto Bukti</label>
                            <div class="mt-2">
                                <img src="{{ $selectedReport->photo }}" alt="Foto bukti"
                                    class="max-w-full rounded-lg border border-neutral-200">
                            </div>
                        </div>
                    @endif

                    {{-- Update Status --}}
                    <div class="border-t border-neutral-200 pt-4">
                        <label class="text-sm font-medium text-neutral-600">Ubah Status</label>
                        <div class="mt-2 flex gap-2">
                            <button wire:click="updateStatus('diproses')"
                                class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                                Diproses
                            </button>
                            <button wire:click="updateStatus('selesai')"
                                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                                Selesai
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 border-t border-neutral-200 p-6">
                    <button @click="$wire.closeDetailModal()"
                        class="rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif