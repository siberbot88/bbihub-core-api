<div class="w-full px-2 lg:px-4 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Verifikasi Bengkel</h1>
            <p class="mt-1 text-sm text-gray-500">Review dan setujui pendaftaran bengkel baru</p>
        </div>
    </div>

    @if (session('message'))
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-emerald-800 animate-fade-in-down">
            <div class="flex gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 shrink-0">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div class="font-semibold text-gray-900">Antrian Verifikasi</div>
            <div class="text-sm text-gray-500">Total: {{ $workshops->total() }}</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500">Bengkel</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500">Pemilik & Kontak</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500">Lokasi</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500">Dokumen</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500">Tanggal Daftar</th>
                        <th class="px-6 py-4 text-right font-semibold text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($workshops as $w)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 shrink-0 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold overflow-hidden border border-indigo-200">
                                        @if($w->photo && !str_contains($w->photo, 'placehold.co'))
                                            <img src="{{ $w->photo }}" class="h-full w-full object-cover">
                                        @else
                                            {{ strtoupper(mb_substr($w->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $w->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $w->code ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $w->email }}</div>
                                <div class="text-xs text-gray-500">{{ $w->phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $w->city }}, {{ $w->province }}
                            </td>
                            <td class="px-6 py-4">
                                <button type="button" wire:click="openDocuments('{{ $w->id }}')" class="text-blue-600 hover:text-blue-700 hover:underline text-xs font-semibold cursor-pointer">
                                    Lihat Dokumen
                                </button>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $w->created_at->format('d M Y') }}
                                <div class="text-xs text-gray-400">{{ $w->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        wire:click="promptReject('{{ $w->id }}')"
                                        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 hover:border-red-300 transition-colors"
                                    >
                                        Tolak
                                    </button>
                                    <button 
                                        wire:click="promptVerify('{{ $w->id }}')" 
                                        class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-emerald-700 transition-colors"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                        </svg>
                                        Verifikasi
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-gray-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p>Tidak ada antrian bengkel untuk diverifikasi saat ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="border-t border-gray-100 px-6 py-4">
            {{ $workshops->links() }}
        </div>
    </div>

    {{-- Document Modal --}}
    @if($showDocuments)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeDocuments"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Dokumen Legalitas Bengkel
                        </h3>
                        <button wire:click="closeDocuments" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                        @if($selectedDocuments)
                            <div class="space-y-6">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        NPWP (Nomor Pokok Wajib Pajak)
                                    </h4>
                                    @if(!empty($selectedDocuments->npwp))
                                        <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                                            <p class="text-lg font-mono font-semibold text-gray-800 tracking-wide">{{ $selectedDocuments->npwp }}</p>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-400">Salin nomor untuk pengecekan validitas.</div>
                                    @else
                                        <div class="rounded-lg border-2 border-dashed border-gray-300 p-4 text-center text-gray-500 bg-gray-50">
                                            Nomor NPWP tidak tersedia.
                                        </div>
                                    @endif
                                </div>
                                <hr class="border-gray-200">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        NIB (Nomor Induk Berusaha)
                                    </h4>
                                    @if(!empty($selectedDocuments->nib))
                                        <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                                            <p class="text-lg font-mono font-semibold text-gray-800 tracking-wide">{{ $selectedDocuments->nib }}</p>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-400">Salin nomor untuk pengecekan validitas.</div>
                                    @else
                                        <div class="rounded-lg border-2 border-dashed border-gray-300 p-4 text-center text-gray-500 bg-gray-50">
                                            Nomor NIB tidak tersedia.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-10">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Data Dokumen Kosong</h3>
                                <p class="mt-1 text-sm text-gray-500">Bengkel ini belum mengunggah dokumen legalitas apapun.</p>
                            </div>
                        @endif
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeDocuments" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Verification Confirmation Modal --}}
    @if($showVerifyModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="resetConfirmation"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Konfirmasi Verifikasi
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah Anda yakin ingin memverifikasi bengkel <span class="font-bold text-gray-800">{{ $workshopToProcess->name ?? 'ini' }}</span>?
                                    Bengkel ini akan segera aktif dan dapat menerima pesanan.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="confirmVerify" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Verifikasi Sekarang
                        </button>
                        <button type="button" wire:click="resetConfirmation" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Rejection Confirmation Modal --}}
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="resetConfirmation"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Konfirmasi Penolakan
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah Anda yakin ingin menolak bengkel <span class="font-bold text-gray-800">{{ $workshopToProcess->name ?? 'ini' }}</span>?
                                    Tindakan ini akan mengubah status bengkel menjadi rejected.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="confirmReject" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Tolak Permohonan
                        </button>
                        <button type="button" wire:click="resetConfirmation" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>