<div class="space-y-6">
  {{-- Header + filter bar --}}
  <div class="flex flex-col gap-3">
    <div>
      <h1 class="text-xl font-semibold">Manajemen Pengguna</h1>
      <p class="text-sm text-neutral-500">
        Ringkasan kondisi akun pengguna dan komunitas aplikasi
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      {{-- Search --}}
      <div class="relative">
        <input
          type="text"
          wire:model.live.debounce.400ms="q"
          placeholder="Cari Pengguna…"
          class="h-9 w-64 rounded-lg border-neutral-300 ps-9 focus:border-red-400 focus:ring-red-400"
        />
        <span class="pointer-events-none absolute inset-y-0 start-2 flex items-center text-neutral-400">
          <img src="{{ asset('icons/search.svg') }}" alt="Search" class="h-5 w-5" />
        </span>
      </div>

      {{-- Filter Status --}}
      <select
        wire:model.live="status"
        class="h-9 rounded-lg border-neutral-300 focus:border-red-400 focus:ring-red-400"
      >
        @foreach ($this->statusOptions as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
      </select>

      {{-- Filter Role --}}
      <select
        wire:model.live="role"
        class="h-9 rounded-lg border-neutral-300 focus:border-red-400 focus:ring-red-400"
      >
        @foreach ($this->roleOptions as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
      </select>

      <div class="ms-auto">
        <a
          href="{{ route('admin.users.create') }}"
          class="flex h-9 items-center justify-center rounded-lg bg-red-600 px-3 text-white shadow hover:bg-red-700"
        >
          + Tambah Pengguna
        </a>
      </div>
    </div>
  </div>

  {{-- 📊 Cards ringkasan per role --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
    <x-summary-card label="Total Pengguna" :value="$totalUsers" hint="update +5%" icon="total_user" color="blue" />
    <x-summary-card label="Menunggu verifikasi" :value="$totalPending" hint="update +2%" icon="total_verifikasi" color="yellow" />
    <x-summary-card label="Akun Aktif" :value="$totalActive" hint="update +5%" icon="akun_aktif" color="green" />
    <x-summary-card label="Total Mekanik" :value="$totalMechanic" hint="update +5%" icon="total_mekanik" color="violet" />
    <x-summary-card label="Total Owner Bengkel" :value="$totalOwner" hint="update +5%" icon="total_bengkel" color="purple" />
    <x-summary-card label="Akun Tidak Aktif" :value="$totalInactive" hint="update +5%" icon="akun_nonaktif" color="red" />
  </div>

  {{-- 🧾 Tabel daftar pengguna --}}
  <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
    <div class="flex items-center justify-between border-b px-4 py-3 text-sm">
      <div class="font-medium">Daftar Pengguna</div>
      <div class="text-neutral-500">
        Total: {{ number_format($totalUsers) }} Pengguna
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-neutral-200 text-sm">
        <thead class="bg-neutral-50">
          <tr class="text-neutral-600">
            <th class="px-4 py-3 text-left">Pengguna</th>
            <th class="px-4 py-3 text-left">Role</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Login Terakhir</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-neutral-100">
          @forelse ($users as $u)
            <tr class="hover:bg-neutral-50">
              {{-- Nama & Email --}}
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div
                    class="grid h-9 w-9 place-items-center rounded-full bg-neutral-200 text-sm font-semibold text-neutral-700"
                  >
                    {{ strtoupper(mb_substr($u->name ?? 'U', 0, 1)) }}
                  </div>
                  <div>
                    <div class="font-medium">{{ $u->name }}</div>
                    <div class="text-xs text-neutral-500">{{ $u->email }}</div>
                  </div>
                </div>
              </td>

              {{-- Role --}}
              <td class="px-4 py-3">
                @if (method_exists($u, 'getRoleNames'))
                  @foreach ($u->getRoleNames() as $r)
                    <span
                      class="me-1 rounded-md bg-violet-100 px-2 py-0.5 text-xs text-violet-700"
                    >
                      {{ $r }}
                    </span>
                  @endforeach
                @else
                  <span
                    class="rounded-md bg-neutral-100 px-2 py-0.5 text-xs text-neutral-600"
                    >-</span
                  >
                @endif
              </td>

              {{-- Status --}}
              <td class="px-4 py-3">
                @php
                  $isActive = false;
                  if (isset($u->status)) {
                      $isActive = $u->status === 'active';
                  } elseif (isset($u->email_verified_at)) {
                      $isActive = (bool) $u->email_verified_at;
                  }
                @endphp

                @if ($isActive)
                  <span
                    class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700"
                    >AKTIF</span
                  >
                @else
                  <span
                    class="rounded-md bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-700"
                    >Nonaktif</span
                  >
                @endif
              </td>

              {{-- Login terakhir --}}
              <td class="px-4 py-3">
                @php
                  $last = $u->last_login_at ?? $u->updated_at ?? null;
                @endphp
                <span class="text-neutral-600">
                  {{ $last ? \Illuminate\Support\Carbon::parse($last)->diffForHumans() : '-' }}
                </span>
              </td>

              {{-- Aksi --}}
              <td class="px-4 py-3">
                <div class="flex items-center gap-3 text-neutral-500">
                  {{-- Lihat --}}
                  <button
                    type="button"
                    wire:click="view('{{ $u->id }}')"
                    class="hover:opacity-80"
                    title="Lihat"
                  >
                    <img
                      src="{{ asset('icons/aksi_detail.svg') }}"
                      alt="Lihat"
                      class="h-5 w-5"
                    />
                  </button>

                  {{-- Edit --}}
                  <button
                    type="button"
                    wire:click="edit('{{ $u->id }}')"
                    class="hover:opacity-80"
                    title="Edit"
                  >
                    <img
                      src="{{ asset('icons/aksi_edit.svg') }}"
                      alt="Edit"
                      class="h-5 w-5"
                    />
                  </button>

                  {{-- Reset Password --}}
                  <button
                    type="button"
                    wire:click="resetPassword('{{ $u->id }}')"
                    class="hover:opacity-80"
                    title="Reset Password"
                  >
                    <img
                      src="{{ asset('icons/aksi_resetpassword.svg') }}"
                      alt="Reset Password"
                      class="h-5 w-5"
                    />
                  </button>

                  {{-- Hapus --}}
                  <button
                    type="button"
                    wire:click="delete('{{ $u->id }}')"
                    class="hover:opacity-80"
                    title="Hapus"
                  >
                    <img
                      src="{{ asset('icons/aksi_hapus.svg') }}"
                      alt="Hapus"
                      class="h-5 w-5"
                    />
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td
                colspan="5"
                class="px-4 py-6 text-center text-neutral-500"
              >
                Tidak ada data.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Modal Detail --}}
      <x-modal name="detail-user" wire:model="showDetail" maxWidth="md">
        @if ($selectedUser)
            <div class="bg-white">
                {{-- Header --}}
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center space-x-3">
                        {{-- Avatar --}}
                        <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-white text-lg font-semibold">
                            {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ $selectedUser->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $selectedUser->email }}</p>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="px-6 py-5">
                    <div class="space-y-4">
                        {{-- Role --}}
                        <div class="flex items-start">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-xs text-gray-500 mb-1">Role</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedUser->roles->pluck('name')->join(', ') }}</p>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="flex items-start">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-xs text-gray-500 mb-1">Status</p>
                                @if($selectedUser->status)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $selectedUser->status }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </div>
                        </div>

                        {{-- Created Date --}}
                        <div class="flex items-start">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-xs text-gray-500 mb-1">Dibuat</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedUser->created_at->diffForHumans() }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $selectedUser->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                    <div class="flex justify-end">
                        <button
                            type="button"
                            wire:click="$set('showDetail', false)"
                            class="px-4 py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </x-modal>

    {{-- Modal Edit --}}
    <x-modal name="edit-user" wire:model="showEdit" maxWidth="md">
      @if ($selectedUser)
          <div class="bg-white">
              {{-- Header --}}
              <div class="border-b border-gray-200 px-6 py-4">
                  <div class="flex items-center space-x-3">
                      {{-- Avatar --}}
                      <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-white text-lg font-semibold">
                          {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                      </div>
                      <div>
                          <h2 class="text-lg font-semibold text-gray-900">Edit Pengguna</h2>
                          <p class="text-sm text-gray-500">Perbarui informasi pengguna</p>
                      </div>
                  </div>
              </div>

              {{-- Form Content --}}
              <form wire:submit.prevent="updateUser">
                  <div class="px-6 py-5 space-y-5">
                      {{-- Nama Field --}}
                      <div class="space-y-2">
                          <label class="flex items-center text-sm font-medium text-gray-700">
                              <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                              Nama
                          </label>
                          <input
                              type="text"
                              wire:model.defer="selectedUser.name"
                              class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                              placeholder="Masukkan nama lengkap"
                          />
                      </div>

                      {{-- Email Field --}}
                      <div class="space-y-2">
                          <label class="flex items-center text-sm font-medium text-gray-700">
                              <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                              </svg>
                              Email
                          </label>
                          <input
                              type="email"
                              wire:model.defer="selectedUser.email"
                              class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                              placeholder="Masukkan alamat email"/>
                      </div>

                      {{-- Password Field --}}
                      <div class="space-y-2">
                          <label class="flex items-center text-sm font-medium text-gray-700">
                              <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                              </svg>
                              Konfirmasi Kata Sandi <span class="text-red-500 ml-1">*</span>
                          </label>
                          <input
                              type="password"
                              wire:model.defer="confirmPassword"
                              class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                              placeholder="Masukkan kata sandi Anda untuk konfirmasi"
                              required />
                          <p class="text-xs text-gray-500 mt-1">Masukkan kata sandi Anda untuk mengonfirmasi perubahan</p>
                      </div>
                  </div>

                  {{-- Footer --}}
                  <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                      <div class="flex justify-end gap-3">
                          <button
                              type="button"
                              wire:click="$set('showEdit', false)"
                              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                              Batal
                          </button>
                          <button
                              type="submit"
                              class="px-4 py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                              Simpan Perubahan
                          </button>
                      </div>
                  </div>
              </form>
          </div>
      @endif
  </x-modal>

    {{-- Modal Reset Password --}}
    <x-modal name="reset-user" wire:model="showReset" maxWidth="md">
      @if ($selectedUser)
          <div class="bg-white">
              {{-- Header --}}
              <div class="border-b border-gray-200 px-6 py-4">
                  <div class="flex items-center space-x-3">
                      {{-- Avatar --}}
                      <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-white text-lg font-semibold">
                          {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                      </div>
                      <div>
                          <h2 class="text-lg font-semibold text-gray-900">Reset Kata Sandi</h2>
                          <p class="text-sm text-gray-500">Ubah kata sandi untuk {{ $selectedUser->name }}</p>
                      </div>
                  </div>
              </div>

              {{-- Form Content --}}
              <form wire:submit.prevent="updatePassword">
                  <div class="px-6 py-5 space-y-5">
                      {{-- Old Password Field --}}
                      <div class="space-y-2">
                          <label class="flex items-center text-sm font-medium text-gray-700">
                              <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                              </svg>
                              Kata Sandi Lama <span class="text-red-500 ml-1">*</span>
                          </label>
                          <input
                              type="password"
                              wire:model.defer="oldPassword"
                              class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                              placeholder="Masukkan kata sandi lama"
                              required />
                          @error('oldPassword')
                              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                          @enderror
                      </div>

                      {{-- New Password Field --}}
                      <div class="space-y-2">
                          <label class="flex items-center text-sm font-medium text-gray-700">
                              <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                              </svg>
                              Kata Sandi Baru <span class="text-red-500 ml-1">*</span>
                          </label>
                          <input
                              type="password"
                              wire:model.defer="newPassword"
                              class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                              placeholder="Masukkan kata sandi baru"
                              required />
                          @error('newPassword')
                              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                          @enderror
                      </div>

                      {{-- Confirm Password Field --}}
                      <div class="space-y-2">
                          <label class="flex items-center text-sm font-medium text-gray-700">
                              <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                              </svg>
                              Konfirmasi Kata Sandi Baru <span class="text-red-500 ml-1">*</span>
                          </label>
                          <input
                              type="password"
                              wire:model.defer="confirmPassword"
                              class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                              placeholder="Ulangi kata sandi baru"
                              required />
                          @error('confirmPassword')
                              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                          @enderror
                      </div>
                  </div>

                  {{-- Footer --}}
                  <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                      <div class="flex justify-end gap-3">
                          <button
                              type="button"
                              wire:click="$set('showReset', false)"
                              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                              Batal
                          </button>
                          <button
                              type="submit"
                              class="px-4 py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                              Simpan Kata Sandi
                          </button>
                      </div>
                  </div>
              </form>
          </div>
      @endif
    </x-modal>

    {{-- Modal Hapus --}}
    <x-modal name="delete-user" wire:model="showDelete" maxWidth="md">
      @if ($selectedUser)
          <div class="bg-white">
              {{-- Header --}}
              <div class="border-b border-gray-200 px-6 py-4">
                  <div class="flex items-center space-x-3">
                      {{-- Avatar --}}
                      <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                          </svg>
                      </div>
                      <div>
                          <h2 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus</h2>
                          <p class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                      </div>
                  </div>
              </div>

              {{-- Content --}}
              <div class="px-6 py-5">
                  <div class="flex items-start space-x-3">
                      <div class="flex-shrink-0">
                          <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                              </svg>
                          </div>
                      </div>
                      <div class="flex-1">
                          <p class="text-sm text-gray-700">
                              Apakah Anda yakin ingin menghapus pengguna
                          </p>
                          <p class="text-base font-semibold text-gray-900 mt-1">
                              {{ $selectedUser->name }}
                          </p>
                          <p class="text-xs text-gray-500 mt-1">
                              {{ $selectedUser->email }}
                          </p>
                          <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                              <p class="text-xs text-red-800">
                                  <strong>Peringatan:</strong> Semua data yang terkait dengan pengguna ini akan dihapus secara permanen.
                              </p>
                          </div>
                      </div>
                  </div>
              </div>

              {{-- Footer --}}
              <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                  <div class="flex justify-end gap-3">
                      <button
                          type="button"
                          wire:click="$set('showDelete', false)"
                          class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                          Batal
                      </button>
                      <button
                          type="button"
                          wire:click="confirmDelete({{ $selectedUser->id }})"
                          class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                          Ya, Hapus Pengguna
                      </button>
                  </div>
              </div>
          </div>
      @endif
    </x-modal>

    {{-- Pagination + perPage --}}
    <div class="flex items-center justify-between border-t px-4 py-3 text-sm">
      <div class="flex items-center gap-2">
        <span class="text-neutral-500">Tampil</span>
        <select
          wire:model.live="perPage"
          class="h-8 rounded-md border-neutral-300 focus:border-red-400 focus:ring-red-400"
        >
          @foreach ([8, 10, 20, 30, 50] as $n)
            <option value="{{ $n }}">{{ $n }}</option>
          @endforeach
        </select>
        <span class="text-neutral-500">baris</span>
      </div>
      <div>
        {{ $users->onEachSide(1)->links() }}
      </div>
    </div>
  </div>

</div>
