<div x-cloak>
  {{-- =========================
      CREATE MODAL
  ========================= --}}
  <div x-data="{ show: @entangle('showCreate') }"
       x-show="show"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 overflow-y-auto"
       style="display:none;">

    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <div @click="$wire.closeModal()" class="fixed inset-0 bg-gray-900/75 transition-opacity"></div>

      <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
        <form wire:submit.prevent="createWorkshop">
          <div class="bg-white px-6 pt-6 pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900">Tambah Bengkel</h3>
              <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              {{-- user_uuid (owner) --}}
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Owner UUID (user_uuid)</label>
                <input type="text" wire:model.defer="user_uuid"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('user_uuid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-500">Default: user login saat ini. Kalau ingin beda owner, isi UUID user lain.</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bengkel</label>
                <input type="text" wire:model.defer="name"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                <input type="text" wire:model.defer="code"
                       placeholder="Kosongkan untuk auto-generate"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:model.defer="status" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  <option value="pending">Pending</option>
                  <option value="active">Active</option>
                  <option value="suspended">Suspended</option>
                  <option value="rejected">Rejected</option>
                </select>
                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="flex items-center gap-3 mt-6">
                <input type="checkbox" wire:model.defer="is_active" class="rounded border-gray-300">
                <span class="text-sm text-gray-700">is_active</span>
                @error('is_active') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea rows="3" wire:model.defer="description"
                          class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400"></textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea rows="2" wire:model.defer="address"
                          class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400"></textarea>
                @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" wire:model.defer="phone"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" wire:model.defer="email"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Photo (URL/path)</label>
                <input type="text" wire:model.defer="photo"
                       placeholder="Kosongkan untuk auto placeholder"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                <input type="text" wire:model.defer="city"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                <input type="text" wire:model.defer="province"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('province') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Negara</label>
                <input type="text" wire:model.defer="country"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('country') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                <input type="text" wire:model.defer="postal_code"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('postal_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                <input type="text" wire:model.defer="latitude"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('latitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                <input type="text" wire:model.defer="longitude"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('longitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Maps URL (opsional)</label>
                <input type="text" wire:model.defer="maps_url"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('maps_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Opening Time</label>
                <input type="time" wire:model.defer="opening_time"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('opening_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Closing Time</label>
                <input type="time" wire:model.defer="closing_time"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('closing_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Operational Days</label>
                <input type="text" wire:model.defer="operational_days"
                       placeholder="Contoh: Senin-Sabtu / Senin,Selasa,Rabu"
                       class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                @error('operational_days') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>

          <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button type="button" wire:click="closeModal"
                    class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Batal
            </button>
            <button type="submit"
                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>


  {{-- =========================
      EDIT MODAL
  ========================= --}}
  <div x-data="{ show: @entangle('showEdit') }"
       x-show="show"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 overflow-y-auto"
       style="display:none;">

    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <div @click="$wire.closeModal()" class="fixed inset-0 bg-gray-900/75 transition-opacity"></div>

      <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
        @if($selectedWorkshop)
          <form wire:submit.prevent="updateWorkshop">
            <div class="bg-white px-6 pt-6 pb-4">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Edit Bengkel</h3>
                <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>

              {{-- Form sama seperti CREATE --}}
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Owner UUID (user_uuid)</label>
                  <input type="text" wire:model.defer="user_uuid"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('user_uuid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bengkel</label>
                  <input type="text" wire:model.defer="name"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                  <input type="text" wire:model.defer="code"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select wire:model.defer="status" class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="rejected">Rejected</option>
                  </select>
                  @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 mt-6">
                  <input type="checkbox" wire:model.defer="is_active" class="rounded border-gray-300">
                  <span class="text-sm text-gray-700">is_active</span>
                  @error('is_active') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                  <textarea rows="3" wire:model.defer="description"
                            class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400"></textarea>
                  @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                  <textarea rows="2" wire:model.defer="address"
                            class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400"></textarea>
                  @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                  <input type="text" wire:model.defer="phone"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input type="email" wire:model.defer="email"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Photo (URL/path)</label>
                  <input type="text" wire:model.defer="photo"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                  <input type="text" wire:model.defer="city"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                  <input type="text" wire:model.defer="province"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('province') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Negara</label>
                  <input type="text" wire:model.defer="country"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('country') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                  <input type="text" wire:model.defer="postal_code"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('postal_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                  <input type="text" wire:model.defer="latitude"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('latitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                  <input type="text" wire:model.defer="longitude"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('longitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Maps URL (opsional)</label>
                  <input type="text" wire:model.defer="maps_url"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('maps_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Opening Time</label>
                  <input type="time" wire:model.defer="opening_time"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('opening_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Closing Time</label>
                  <input type="time" wire:model.defer="closing_time"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('closing_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Operational Days</label>
                  <input type="text" wire:model.defer="operational_days"
                         class="w-full rounded-xl border-gray-300 focus:border-red-400 focus:ring-red-400">
                  @error('operational_days') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
              </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
              <button type="button" wire:click="closeModal"
                      class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Batal
              </button>
              <button type="submit"
                      class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Perbarui
              </button>
            </div>
          </form>
        @endif
      </div>
    </div>
  </div>


  {{-- =========================
      DETAIL / VIEW MODAL
  ========================= --}}
  <div x-data="{ show: @entangle('showDetail') }"
       x-show="show"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 overflow-y-auto"
       style="display:none;">

    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <div @click="$wire.closeModal()" class="fixed inset-0 bg-gray-900/75 transition-opacity"></div>

      <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
        @if($selectedWorkshop)
          <div class="bg-white px-6 pt-6 pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900">Detail Bengkel</h3>
              <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
                  <p class="text-sm text-gray-500">Kode: {{ $selectedWorkshop->code }}</p>
                </div>
              </div>

              <div class="border-t border-gray-100 pt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="flex justify-between md:col-span-2 py-2">
                  <span class="text-sm font-medium text-gray-500">Owner UUID:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->user_uuid }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Status:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->status }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Aktif:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->is_active ? 'Ya' : 'Tidak' }}</span>
                </div>

                <div class="md:col-span-2 py-2">
                  <div class="text-sm font-medium text-gray-500">Deskripsi:</div>
                  <div class="mt-1 text-sm text-gray-900">{{ $selectedWorkshop->description }}</div>
                </div>

                <div class="md:col-span-2 py-2">
                  <div class="text-sm font-medium text-gray-500">Alamat:</div>
                  <div class="mt-1 text-sm text-gray-900">{{ $selectedWorkshop->address }}</div>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Phone:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->phone }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Email:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->email }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Kota:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->city }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Provinsi:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->province }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Negara:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->country }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Kode Pos:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->postal_code }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Latitude:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->latitude }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Longitude:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->longitude }}</span>
                </div>

                <div class="flex justify-between md:col-span-2 py-2">
                  <span class="text-sm font-medium text-gray-500">Maps URL:</span>
                  @if($selectedWorkshop->maps_url)
                    <a href="{{ $selectedWorkshop->maps_url }}" target="_blank" class="text-sm font-semibold text-blue-600 hover:underline">Buka</a>
                  @else
                    <span class="text-sm font-semibold text-gray-900">-</span>
                  @endif
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Buka:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->opening_time }}</span>
                </div>

                <div class="flex justify-between py-2">
                  <span class="text-sm font-medium text-gray-500">Tutup:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->closing_time }}</span>
                </div>

                <div class="flex justify-between md:col-span-2 py-2">
                  <span class="text-sm font-medium text-gray-500">Operational Days:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->operational_days }}</span>
                </div>

                <div class="flex justify-between md:col-span-2 py-2">
                  <span class="text-sm font-medium text-gray-500">Dibuat:</span>
                  <span class="text-sm font-semibold text-gray-900">{{ $selectedWorkshop->created_at?->format('d M Y, H:i') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 px-6 py-4 flex justify-end">
            <button type="button" wire:click="closeModal"
                    class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Tutup
            </button>
          </div>
        @endif
      </div>
    </div>
  </div>


  {{-- =========================
      SUSPEND MODAL
  ========================= --}}
  <div x-data="{ show: @entangle('showSuspend') }"
       x-show="show"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 overflow-y-auto"
       style="display:none;">

    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <div @click="$wire.closeModal()" class="fixed inset-0 bg-gray-900/75 transition-opacity"></div>

      <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        @if($selectedWorkshop)
          <div class="bg-white px-6 pt-6 pb-4">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
                <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>

              <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900">
                  Konfirmasi {{ $selectedWorkshop->status === 'suspended' ? 'Aktifkan' : 'Suspend' }}
                </h3>

                <div class="mt-2 text-sm text-gray-500">
                  @if($selectedWorkshop->status === 'suspended')
                    <p>Aktifkan kembali bengkel <strong class="text-gray-900">{{ $selectedWorkshop->name }}</strong>?</p>
                  @else
                    <p>Suspend bengkel <strong class="text-gray-900">{{ $selectedWorkshop->name }}</strong>?</p>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button type="button" wire:click="closeModal"
                    class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Batal
            </button>
            <button type="button" wire:click="confirmSuspend"
                    class="rounded-xl bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
              Ya, {{ $selectedWorkshop->status === 'suspended' ? 'Aktifkan' : 'Suspend' }}
            </button>
          </div>
        @endif
      </div>
    </div>
  </div>


  {{-- =========================
      DELETE MODAL
  ========================= --}}
  <div x-data="{ show: @entangle('showDelete') }"
       x-show="show"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 overflow-y-auto"
       style="display:none;">

    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <div @click="$wire.closeModal()" class="fixed inset-0 bg-gray-900/75 transition-opacity"></div>

      <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        @if($selectedWorkshop)
          <div class="bg-white px-6 pt-6 pb-4">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>

              <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
                <div class="mt-2 text-sm text-gray-500">
                  <p>Hapus bengkel <strong class="text-gray-900">{{ $selectedWorkshop->name }}</strong>?</p>
                  <p class="mt-2">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button type="button" wire:click="closeModal"
                    class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Batal
            </button>
            <button type="button" wire:click="confirmDelete"
                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
              Ya, Hapus
            </button>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
