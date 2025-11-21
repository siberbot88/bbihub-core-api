<div class="space-y-6">
  {{-- Header + filter bar --}}
  <div class="flex flex-col gap-3">
    <div>
      <h1 class="text-xl font-semibold">Manajemen Pengguna</h1>
      <p class="text-sm text-neutral-500">Ringkasan kondisi akun pengguna dan komunitas aplikasi</p>
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
        <img src="{{ asset('icons/search.svg') }}" alt="Search" class="h-4 w-4" />
      </span>
      </div>

      {{-- Filter Status --}}
      <select wire:model.live="status"
              class="h-9 rounded-lg border-neutral-300 focus:border-red-400 focus:ring-red-400">
        @foreach($this->statusOptions as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
      </select>

      {{-- Filter Role --}}
      <select wire:model.live="role"
              class="h-9 rounded-lg border-neutral-300 focus:border-red-400 focus:ring-red-400">
        @foreach($this->roleOptions as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
      </select>

      <div class="ms-auto">
        <a href="{{ route('admin.users.create') }}"
          class="h-9 rounded-lg bg-red-600 px-3 text-white shadow hover:bg-red-700 flex items-center justify-center">
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
      <div class="text-neutral-500">Total: {{ number_format($totalUsers) }} Pengguna</div>
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
          @forelse($users as $u)
            <tr class="hover:bg-neutral-50">
              {{-- Nama & Email --}}
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="grid h-9 w-9 place-items-center rounded-full bg-neutral-200 text-sm font-semibold text-neutral-700">
                    {{ strtoupper(mb_substr($u->name ?? 'U',0,1)) }}
                  </div>
                  <div>
                    <div class="font-medium">{{ $u->name }}</div>
                    <div class="text-xs text-neutral-500">{{ $u->email }}</div>
                  </div>
                </div>
              </td>

              {{-- Role --}}
              <td class="px-4 py-3">
                @if(method_exists($u,'getRoleNames'))
                  @foreach($u->getRoleNames() as $r)
                    <span class="me-1 rounded-md bg-violet-100 px-2 py-0.5 text-xs text-violet-700">{{ $r }}</span>
                  @endforeach
                @else
                  <span class="rounded-md bg-neutral-100 px-2 py-0.5 text-xs text-neutral-600">-</span>
                @endif
              </td>

              {{-- Status --}}
              <td class="px-4 py-3">
                @php
                  $isActive = false;
                  if (isset($u->status)) $isActive = $u->status === 'active';
                  elseif (isset($u->email_verified_at)) $isActive = (bool) $u->email_verified_at;
                @endphp
                @if($isActive)
                  <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">AKTIF</span>
                @else
                  <span class="rounded-md bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-700">Nonaktif</span>
                @endif
              </td>

              {{-- Login terakhir --}}
              <td class="px-4 py-3">
                @php $last = $u->last_login_at ?? $u->updated_at ?? null; @endphp
                <span class="text-neutral-600">
                  {{ $last ? \Illuminate\Support\Carbon::parse($last)->diffForHumans() : '-' }}
                </span>
              </td>

              {{-- Aksi --}}
              <td class="px-4 py-3">
                <div class="flex items-center gap-3 text-neutral-500">
                  {{-- Lihat --}}
                  <button wire:click="view({{ $u->id }})" class="hover:opacity-80" title="Lihat">
                    <img src="{{ asset('icons/aksi_detail.svg') }}" alt="Lihat" class="h-5 w-5">
                  </button>

                  {{-- Edit --}}
                  <button wire:click="edit({{ $u->id }})" class="hover:opacity-80" title="Edit">
                    <img src="{{ asset('icons/aksi_edit.svg') }}" alt="Edit" class="h-5 w-5">
                  </button>

                  {{-- Reset Password --}}
                  <button wire:click="resetPassword({{ $u->id }})" class="hover:opacity-80" title="Reset Password">
                    <img src="{{ asset('icons/aksi_resetpassword.svg') }}" alt="Reset Password" class="h-5 w-5">
                  </button>

                  {{-- Hapus --}}
                  <button wire:click="delete({{ $u->id }})" class="hover:opacity-80" title="Hapus">
                    <img src="{{ asset('icons/aksi_hapus.svg') }}" alt="Hapus" class="h-5 w-5">
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-neutral-500">Tidak ada data.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Modal Detail --}}
    <x-modal name="detail-user" :show="$showDetail">
      @if($selectedUser)
        <div class="space-y-2 text-sm">
          <div><strong>Nama:</strong> {{ $selectedUser->name }}</div>
          <div><strong>Email:</strong> {{ $selectedUser->email }}</div>
          <div><strong>Role:</strong> {{ $selectedUser->roles->pluck('name')->join(', ') }}</div>
          <div><strong>Status:</strong> {{ $selectedUser->status ?? '-' }}</div>
          <div><strong>Dibuat:</strong> {{ $selectedUser->created_at->diffForHumans() }}</div>
        </div>
      @endif
    </x-modal>

    {{-- Modal Edit --}}
    <x-modal name="edit-user" :show="$showEdit">
      @if($selectedUser)
        <form wire:submit.prevent="updateUser">
          <input type="hidden" wire:model="selectedUser.id">
          <div class="space-y-3">
            <input type="text" wire:model="selectedUser.name" class="w-full border rounded px-3 py-2" placeholder="Nama">
            <input type="email" wire:model="selectedUser.email" class="w-full border rounded px-3 py-2" placeholder="Email">
          </div>
          <div class="mt-4 flex justify-end gap-2">
            <button type="button" wire:click="$set('showEdit', false)" class="px-3 py-2 border rounded">Batal</button>
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
          </div>
        </form>
      @endif
    </x-modal>

    {{-- Modal Reset Password --}}
    <x-modal name="reset-user" :show="$showReset">
      <form wire:submit.prevent="updatePassword">
        <input type="password" wire:model="newPassword" class="w-full border rounded px-3 py-2" placeholder="Password baru">
        <input type="password" wire:model="confirmPassword" class="w-full border rounded px-3 py-2 mt-2" placeholder="Konfirmasi password">
        <div class="mt-4 flex justify-end gap-2">
          <button type="button" wire:click="$set('showReset', false)" class="px-3 py-2 border rounded">Batal</button>
          <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
        </div>
      </form>
    </x-modal>

    {{-- Modal Hapus --}}
    <x-modal name="delete-user" :show="$showDelete">
      @if($selectedUser)
        <p>Apakah kamu yakin ingin menghapus <strong>{{ $selectedUser->name }}</strong>?</p>
        <div class="mt-4 flex justify-end gap-2">
          <button type="button" wire:click="$set('showDelete', false)" class="px-3 py-2 border rounded">Batal</button>
          <button type="button" wire:click="confirmDelete({{ $selectedUser->id }})" class="px-3 py-2 bg-red-600 text-white rounded">Ya, Hapus</button>
        </div>
      @endif
    </x-modal>


    <div class="flex items-center justify-between border-t px-4 py-3 text-sm">
      <div class="flex items-center gap-2">
        <span class="text-neutral-500">Tampil</span>
        <select wire:model.live="perPage"
                class="h-8 rounded-md border-neutral-300 focus:border-red-400 focus:ring-red-400">
          @foreach([8,10,20,30,50] as $n)
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

  {{-- Footer logo --}}
  <div class="rounded-xl border border-neutral-200 bg-white px-5 py-6">
    <div class="text-lg font-semibold">
      <span class="me-2">🛠️</span> BBI HUB <span class="text-red-600">Plus</span>
    </div>
  </div>
</div>
