{{-- VIEW DETAIL MODAL --}}
<div x-data="{ show: @entangle('showDetail') }" 
     x-show="show" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
  
  <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
    <div @click="show = false" class="fixed inset-0 bg-gray-900/75 transition-opacity"></div>

    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      @if($selectedWorkshop)
        <div class="bg-white px-6 pt-6 pb-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Detail Bengkel</h3>
            <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div class="flex items-center gap-4">
              <div class="h-16 w-16 shrink-0 rounded-xl bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center">
                <span class="text-lg font-bold text-red-700">{{ strtoupper(mb_substr($selectedWorkshop->name ?? 'W', 0, 1)) }}</span>
              </div>
              <div>
                <h4 class="font-bold text-gray-900">{{ $selectedWorkshop->name }}</h4>
                <p class="text-sm text-gray-500">ID: {{ $selectedWorkshop->code }}</p>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-4 space-y-3">
              <div class="flex justify-between py-2">
                <span class="text-sm font-medium text-gray-500">Lokasi:</span>
                <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->city ?? '-' }}</span>
              </div>

              <div class="flex justify-between py-2">
                <span class="text-sm font-medium text-gray-500">Status:</span>
                @if(isset($selectedWorkshop->status))
                  @if($selectedWorkshop->status === 'active')
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">Aktif</span>
                  @else
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">{{ ucfirst($selectedWorkshop->status) }}</span>
                  @endif
                @else
                  <span class="text-sm text-gray-900">-</span>
                @endif
              </div>

              <div class="flex justify-between py-2">
                <span class="text-sm font-medium text-gray-500">Dibuat:</span>
                <span class="text-sm text-gray-900">{{ $selectedWorkshop->created_at->format('d M Y, H:i') }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex justify-end">
          <button type="button" wire:click="closeModal" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Tutup
          </button>
        </div>
      @endif
    </div>
  </div>
</div>

{{-- SUSPEND CONFIRMATION MODAL --}}
<div x-data="{ show: @entangle('showSuspend') }" 
     x-show="show" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
  
  <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
    <div @click="show = false" class="fixed inset-0 bg-gray-900/75 transition-opacity"></div>

    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      @if($selectedWorkshop)
        <div class="bg-white px-6 pt-6 pb-4">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
              <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-bold text-gray-900">Konfirmasi {{ isset($selectedWorkshop->status) && $selectedWorkshop->status === 'suspended' ? 'Aktifkan' : 'Suspend' }}</h3>
              <div class="mt-2 text-sm text-gray-500">
                @if(isset($selectedWorkshop->status) && $selectedWorkshop->status === 'suspended')
                  <p>Apakah Anda yakin ingin <strong class="text-emerald-600">mengaktifkan kembali</strong> bengkel <strong class="text-gray-900">{{ $selectedWorkshop->name }}</strong>?</p>
                @else
                  <p>Apakah Anda yakin ingin <strong class="text-orange-600">menangguhkan</strong> bengkel <strong class="text-gray-900">{{ $selectedWorkshop->name }}</strong>?</p>
                @endif
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
          <button type="button" wire:click="closeModal" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Batal
          </button>
          <button type="button" wire:click="confirmSuspend" class="rounded-xl bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
            Ya, {{ isset($selectedWorkshop->status) && $selectedWorkshop->status === 'suspended' ? 'Aktifkan' : 'Suspend' }}
          </button>
        </div>
      @endif
    </div>
  </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div x-data="{ show: @entangle('showDelete') }" 
     x-show="show" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
  
  <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
    <div @click="show = false" class="fixed inset-0 bg-gray-900/75 transition-opacity"></div>

    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      @if($selectedWorkshop)
        <div class="bg-white px-6 pt-6 pb-4">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
              <div class="mt-2 text-sm text-gray-500">
                <p>Apakah Anda yakin ingin menghapus bengkel <strong class="text-gray-900">{{ $selectedWorkshop->name }}</strong>?</p>
                <p class="mt-2">Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait bengkel ini.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
          <button type="button" wire:click="closeModal" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Batal
          </button>
          <button type="button" wire:click="confirmDelete" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
            Ya, Hapus
          </button>
        </div>
      @endif
    </div>
  </div>
</div>
