{{-- CREATE MODAL --}}
<div x-data="{ show: @entangle('showCreate') }" 
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
      <form wire:submit.prevent="createUser">
        <div class="bg-white px-6 pt-6 pb-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Tambah Pengguna Baru</h3>
            <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
              <input type="text" wire:model="form.name" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
              @error('form.name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input type="email" wire:model="form.email" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
              @error('form.email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input type="password" wire:model="form.password" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
              @error('form.password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
              <select wire:model="form.role" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                <option value="">Pilih Role</option>
                <option value="superadmin">Super Admin</option>
                <option value="admin">Admin</option>
                <option value="owner">Owner</option>
                <option value="mechanic">Mekanik</option>
              </select>
              @error('form.role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bengkel (Optional)</label>
              <select wire:model="form.workshop_id" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                <option value="">Pilih Bengkel</option>
                @foreach($workshops as $workshop)
                  <option value="{{ $workshop->id }}">{{ $workshop->name }}</option>
                @endforeach
              </select>
              @error('form.workshop_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
          <button type="button" @click="show = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Batal
          </button>
          <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- EDIT MODAL --}}
<div x-data="{ show: @entangle('showEdit') }" 
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
      @if($selectedUser)
      <form wire:submit.prevent="updateUser">
        <div class="bg-white px-6 pt-6 pb-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Edit Pengguna</h3>
            <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
              <input type="text" wire:model="form.name" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
              @error('form.name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input type="email" wire:model="form.email" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
              @error('form.email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
              <input type="password" wire:model="form.password" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400" placeholder="Kosongkan jika tidak ingin mengubah">
              @error('form.password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
              <select wire:model="form.role" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                <option value="">Pilih Role</option>
                <option value="superadmin">Super Admin</option>
                <option value="admin">Admin</option>
                <option value="owner">Owner</option>
                <option value="mechanic">Mekanik</option>
              </select>
              @error('form.role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bengkel (Optional)</label>
              <select wire:model="form.workshop_id" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                <option value="">Pilih Bengkel</option>
                @foreach($workshops as $workshop)
                  <option value="{{ $workshop->id }}">{{ $workshop->name }}</option>
                @endforeach
              </select>
              @error('form.workshop_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
          <button type="button" @click="show = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Batal
          </button>
          <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
            Perbarui
          </button>
        </div>
      </form>
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
      @if($selectedUser)
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
                <p>Apakah Anda yakin ingin menghapus pengguna <strong class="text-gray-900">{{ $selectedUser->name }}</strong>?</p>
                @if($selectedUser->employment)
                  <p class="mt-2 text-orange-600 font-medium">⚠️ User ini terhubung dengan bengkel: {{ $selectedUser->employment->workshop->name ?? '-' }}</p>
                @endif
                <p class="mt-2">Tindakan ini tidak dapat dibatalkan.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
          <button type="button" @click="show = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
      @if($selectedUser)
        <div class="bg-white px-6 pt-6 pb-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Detail Pengguna</h3>
            <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div class="flex items-center gap-4">
              <div class="h-16 w-16 shrink-0 overflow-hidden rounded-full ring-2 ring-gray-200">
                @if($selectedUser->photo && \Illuminate\Support\Facades\Storage::exists($selectedUser->photo))
                  <img src="{{ \Illuminate\Support\Facades\Storage::url($selectedUser->photo) }}" alt="{{ $selectedUser->name }}" class="h-full w-full object-cover">
                @else
                  <div class="grid h-full w-full place-items-center bg-gradient-to-br from-red-100 to-red-200 text-lg font-bold text-red-700">
                    {{ strtoupper(mb_substr($selectedUser->name ?? 'U', 0, 1)) }}
                  </div>
                @endif
              </div>
              <div>
                <h4 class="font-bold text-gray-900">{{ $selectedUser->name }}</h4>
                <p class="text-sm text-gray-500">{{ $selectedUser->email }}</p>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-4 space-y-3">
              <div class="flex justify-between py-2">
                <span class="text-sm font-medium text-gray-500">Role:</span>
                <span class="text-sm font-semibold text-gray-900">{{ $selectedUser->roles->pluck('name')->join(', ') }}</span>
              </div>

              <div class="flex justify-between py-2">
                <span class="text-sm font-medium text-gray-500">Bengkel:</span>
                <span class="text-sm font-semibold text-gray-900">{{ $selectedUser->employment?->workshop?->name ?? '-' }}</span>
              </div>

              <div class="flex justify-between py-2">
                <span class="text-sm font-medium text-gray-500">Status:</span>
                @php
                  $status = $this->getUserStatus($selectedUser);
                @endphp
                @if($status === 'Aktif')
                  <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">{{ $status }}</span>
                @else
                  <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">{{ $status }}</span>
                @endif
              </div>

              <div class="flex justify-between py-2">
                <span class="text-sm font-medium text-gray-500">Dibuat:</span>
                <span class="text-sm text-gray-900">{{ $selectedUser->created_at->format('d M Y, H:i') }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex justify-end">
          <button type="button" @click="show = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Tutup
          </button>
        </div>
      @endif
    </div>
  </div>
</div>

{{-- RESET PASSWORD MODAL --}}
<div x-data="{ show: @entangle('showReset') }" 
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
      <form wire:submit.prevent="updatePassword">
        <div class="bg-white px-6 pt-6 pb-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Reset Password</h3>
            <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          @if($selectedUser)
            <p class="text-sm text-gray-500 mb-4">Reset password untuk <strong>{{ $selectedUser->name }}</strong></p>
          @endif

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
              <input type="password" wire:model="newPassword" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400" placeholder="Minimal 8 karakter">
              @error('newPassword') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
              <input type="password" wire:model="confirmPassword" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400" placeholder="Ulangi password">
              @error('confirmPassword') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
          <button type="button" @click="show = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Batal
          </button>
          <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
            Reset Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
