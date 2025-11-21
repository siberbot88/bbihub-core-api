
<div class="bg-[#F6EFEF] min-h-screen">
  <div class="mx-auto max-w-7xl px-4 py-6">

    {{-- TOP TOOLBAR (mirip) --}}
    <div class="flex items-center gap-3">
      <div class="relative flex-1">
        <input type="text" placeholder="Cari pengaturan…"
               class="w-full rounded-xl border border-gray-200 bg-white pl-10 pr-3 py-2.5 focus:outline-none focus:ring" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8 4a4 4 0 102.546 7.032l3.71 3.71a1 1 0 001.415-1.414l-3.71-3.71A4 4 0 008 4z" clip-rule="evenodd"/>
        </svg>
      </div>
      <button class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 hover:bg-gray-50">Reset</button>
      <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-white hover:bg-blue-700"
              wire:click="$refresh">Simpan Semua</button>
    </div>

    {{-- Kartu judul --}}
    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-5">
      <h2 class="text-lg font-semibold">Pengaturan</h2>
      <p class="text-sm text-gray-500 -mt-0.5">Atur identitas aplikasi, branding, notifikasi, keamanan, dan lain-lain</p>
    </div>

    {{-- Tab strip (horizontal) --}}
    <div class="mt-4 overflow-x-auto">
      <div class="flex gap-2">
        @php
          $tabs = [
            'general'  => 'Umum',
            'branding' => 'Tampilan & Brand',
            'roles'    => 'Role & Izin',
            'notify'   => 'Notifikasi',
            'security' => 'Keamanan',
            'locale'   => 'Lokalitas',
          ];
        @endphp
        @foreach($tabs as $key => $label)
          <button wire:click="$set('tab','{{ $key }}')"
                  class="rounded-xl px-4 py-2.5 border {{ $tab===$key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
            {{ $label }}
          </button>
        @endforeach
      </div>
    </div>

    {{-- Content panel --}}
    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-5">
      @switch($tab)

        {{-- ========== UMUM ========== --}}
        @case('general')
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="text-sm text-gray-600">Nama Aplikasi</label>
              <input type="text" wire:model.live="app_name" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-gray-600">Tagline</label>
              <input type="text" wire:model.live="app_tagline" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
            <div class="md:col-span-2">
              <label class="text-sm text-gray-600">Email Kontak</label>
              <input type="email" wire:model.live="contact_email" class="mt-1 w-full rounded-lg border px-3 py-2">
            </div>
          </div>
          <div class="mt-5 flex justify-end">
            <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-white" wire:click="saveGeneral">Simpan</button>
          </div>
        @break

        {{-- ========== BRANDING ========== --}}
        @case('branding')
          <div class="grid gap-6 md:grid-cols-2">
            <div>
              <label class="text-sm text-gray-600">Logo (light)</label>
              <input type="file" wire:model="logo_light" class="mt-1 block w-full">
              @if($logo_light)
                <div class="mt-2 text-xs text-gray-500">Preview:</div>
                <img class="mt-1 h-14 rounded-md" src="{{ $logo_light->temporaryUrl() }}">
              @endif
            </div>
            <div>
              <label class="text-sm text-gray-600">Logo (dark)</label>
              <input type="file" wire:model="logo_dark" class="mt-1 block w-full">
              @if($logo_dark)
                <div class="mt-2 text-xs text-gray-500">Preview:</div>
                <img class="mt-1 h-14 rounded-md" src="{{ $logo_dark->temporaryUrl() }}">
              @endif
            </div>
            <div class="md:col-span-2">
              <label class="text-sm text-gray-600">Warna Utama</label>
              <div class="mt-1 flex items-center gap-3">
                <input type="color" wire:model.live="primary_color" class="h-10 w-16 rounded">
                <input type="text" wire:model.live="primary_color" class="flex-1 rounded-lg border px-3 py-2">
              </div>
            </div>
          </div>
          <div class="mt-5 flex justify-end">
            <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-white" wire:click="saveBranding">Simpan</button>
          </div>
        @break

        {{-- ========== ROLES & IZIN ========== --}}
        @case('roles')
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50 text-sm text-gray-600">
                <tr>
                  <th class="px-4 py-3 text-left">Role</th>
                  <th class="px-4 py-3 text-left">Pengguna</th>
                  <th class="px-4 py-3 text-left">Bengkel</th>
                  <th class="px-4 py-3 text-left">Kendaraan</th>
                  <th class="px-4 py-3 text-left">Billing</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm">
                @foreach($roleMatrix as $role => $perm)
                  <tr>
                    <td class="px-4 py-3 font-medium">{{ $role }}</td>
                    @foreach(['users','workshops','vehicles','billing'] as $m)
                      <td class="px-4 py-3">
                        <label class="inline-flex items-center gap-2">
                          <input type="checkbox" wire:model.live="roleMatrix.{{ $role }}.{{ $m }}" class="rounded border-gray-300">
                          <span class="text-gray-600">Akses</span>
                        </label>
                      </td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="mt-5 flex justify-end">
            <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-white" wire:click="saveRoles">Simpan</button>
          </div>
        @break

        {{-- ========== NOTIFIKASI ========== --}}
        @case('notify')
          <div class="grid gap-4">
            <label class="inline-flex items-center gap-3">
              <input type="checkbox" wire:model.live="notif_email" class="rounded border-gray-300">
              <span>Email notifikasi</span>
            </label>
            <label class="inline-flex items-center gap-3">
              <input type="checkbox" wire:model.live="notif_push" class="rounded border-gray-300">
              <span>Push (web/app)</span>
            </label>
            <label class="inline-flex items-center gap-3">
              <input type="checkbox" wire:model.live="notif_whatsapp" class="rounded border-gray-300">
              <span>WhatsApp</span>
            </label>
          </div>
          <div class="mt-5 flex justify-end">
            <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-white" wire:click="saveNotify">Simpan</button>
          </div>
        @break

        {{-- ========== KEAMANAN ========== --}}
        @case('security')
          <div class="grid gap-4 md:grid-cols-2">
            <label class="inline-flex items-center gap-3">
              <input type="checkbox" wire:model.live="force_2fa" class="rounded border-gray-300">
              <span>Wajibkan 2FA untuk semua user</span>
            </label>
            <label class="inline-flex items-center gap-3">
              <input type="checkbox" wire:model.live="single_session" class="rounded border-gray-300">
              <span>Batasi satu sesi login per user</span>
            </label>

            <div class="md:col-span-2 flex items-center gap-3">
              <label class="inline-flex items-center gap-3">
                <input type="checkbox" wire:model.live="password_expiry" class="rounded border-gray-300">
                <span>Perlu reset password berkala</span>
              </label>
              <input type="number" min="30" step="15" wire:model.live="password_days"
                     class="w-28 rounded-lg border px-3 py-2" @disabled(!$password_expiry)>
              <span class="text-sm text-gray-600">hari</span>
            </div>
          </div>
          <div class="mt-5 flex justify-end">
            <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-white" wire:click="saveSecurity">Simpan</button>
          </div>
        @break

        {{-- ========== LOKALITAS ========== --}}
        @case('locale')
          <div class="grid gap-4 md:grid-cols-3">
            <div>
              <label class="text-sm text-gray-600">Zona Waktu</label>
              <select wire:model.live="timezone" class="mt-1 w-full rounded-lg border px-3 py-2">
                @foreach(['Asia/Jakarta','Asia/Makassar','Asia/Jayapura'] as $tz)
                  <option value="{{ $tz }}">{{ $tz }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="text-sm text-gray-600">Bahasa</label>
              <select wire:model.live="locale" class="mt-1 w-full rounded-lg border px-3 py-2">
                <option value="id">Indonesia</option>
                <option value="en">English</option>
              </select>
            </div>
            <div>
              <label class="text-sm text-gray-600">Format Tanggal</label>
              <input type="text" wire:model.live="date_format" class="mt-1 w-full rounded-lg border px-3 py-2" placeholder="d M Y">
            </div>
          </div>
          <div class="mt-5 flex justify-end">
            <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-white" wire:click="saveLocale">Simpan</button>
          </div>
        @break

      @endswitch
    </div>

  </div>
</div>

