<div class="w-full px-2 lg:px-4 space-y-5">
  {{-- Flash Messages --}}
  @if (session()->has('message'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
      <div class="flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium">{{ session('message') }}</span>
      </div>
    </div>
  @endif

  @if (session()->has('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
      <div class="flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
      </div>
    </div>
  @endif

  {{-- Header Section --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Manajemen Pengguna</h1>
      <p class="mt-1 text-sm text-gray-500">Ringkasan kondisi akun pengguna dan komunitas aplikasi</p>
    </div>

    <button wire:click="create" class="flex items-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
      Tambah Pengguna
    </button>
  </div>

  {{-- Filters --}}
  <div class="flex flex-wrap items-center gap-3">
    <div class="relative flex-1 min-w-[250px]">
      <input
        type="text"
        wire:model.live.debounce.400ms="q"
        placeholder="Cari Pengguna..."
        class="h-10 w-full rounded-xl border-gray-200 ps-10 text-sm focus:border-red-400 focus:ring-red-400"
      />
      <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
      </span>
    </div>

    <select wire:model.live="status" class="h-10 rounded-xl border-gray-200 text-sm focus:border-red-400 focus:ring-red-400">
      @foreach ($statusOptions as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
      @endforeach
    </select>

    <select wire:model.live="role" class="h-10 rounded-xl border-gray-200 text-sm focus:border-red-400 focus:ring-red-400">
      @foreach ($roleOptions as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
      @endforeach
    </select>
  </div>

  {{-- Summary Cards --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
          </svg>
        </div>
        <span class="text-xs font-medium text-emerald-600">{{ $growthUsers }}</span>
      </div>
      <div class="mt-3">
        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</div>
        <div class="text-sm text-gray-500">Total Pengguna</div>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="h-12 w-12 rounded-xl bg-yellow-50 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-yellow-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <span class="text-xs font-medium text-emerald-600">{{ $growthPending }}</span>
      </div>
      <div class="mt-3">
        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalPending) }}</div>
        <div class="text-sm text-gray-500">Menunggu Verifikasi</div>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="h-12 w-12 rounded-xl bg-emerald-50 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-emerald-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <span class="text-xs font-medium text-emerald-600">{{ $growthActive }}</span>
      </div>
      <div class="mt-3">
        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalActive) }}</div>
        <div class="text-sm text-gray-500">Akun Aktif</div>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="h-12 w-12 rounded-xl bg-purple-50 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-purple-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
          </svg>
        </div>
        <span class="text-xs font-medium text-emerald-600">+5%</span>
      </div>
      <div class="mt-3">
        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalMechanic) }}</div>
        <div class="text-sm text-gray-500">Total Mekanik</div>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="h-12 w-12 rounded-xl bg-indigo-50 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" />
          </svg>
        </div>
        <span class="text-xs font-medium text-emerald-600">+5%</span>
      </div>
      <div class="mt-3">
        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalOwner) }}</div>
        <div class="text-sm text-gray-500">Total Owner Bengkel</div>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="h-12 w-12 rounded-xl bg-red-50 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-red-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
          </svg>
        </div>
        <span class="text-xs font-medium text-red-600">{{ $growthInactive }}</span>
      </div>
      <div class="mt-3">
        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalInactive) }}</div>
        <div class="text-sm text-gray-500">Akun Tidak Aktif</div>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
      <div class="font-semibold text-gray-900">Daftar Pengguna</div>
      <div class="text-sm text-gray-500">Total: {{ number_format($totalUsers) }} Pengguna</div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr class="text-gray-600">
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pengguna</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Bengkel</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Role</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Login Terakhir</th>
            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100 bg-white">
          @forelse ($users as $u)
            <tr class="hover:bg-gray-50 transition-colors">
              {{-- User Info --}}
              <td class="px-6 py-4">
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full ring-2 ring-white shadow-sm bg-gray-100">
                    @if($u->photo && \Illuminate\Support\Facades\Storage::exists($u->photo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($u->photo) }}" 
                             alt="{{ $u->name }}" 
                             class="h-full w-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                        <div class="hidden h-full w-full place-items-center bg-gradient-to-br from-red-100 to-red-200 text-sm font-bold text-red-700" style="display: none;">
                            {{ strtoupper(mb_substr($u->name ?? 'U', 0, 1)) }}
                        </div>
                    @else
                        <div class="grid h-full w-full place-items-center bg-gradient-to-br from-red-100 to-red-200 text-sm font-bold text-red-700">
                            {{ strtoupper(mb_substr($u->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                  </div>
                  <div>
                    <div class="font-semibold text-gray-900">{{ $u->name }}</div>
                    <div class="text-xs text-gray-500">{{ $u->email }}</div>
                  </div>
                </div>
              </td>

              {{-- Workshop --}}
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  {{ $u->employment?->workshop?->name ?? '-' }}
                </div>
              </td>

              {{-- Role --}}
              <td class="px-6 py-4">
                @if (method_exists($u, 'getRoleNames'))
                  @foreach ($u->getRoleNames() as $r)
                    <span class="me-1 rounded-full bg-violet-100 px-2.5 py-1 text-xs font-medium text-violet-700">
                      {{ $r }}
                    </span>
                  @endforeach
                @else
                  <span class="rounded-full bg-gray-100 px-2.5 py-1 text200xs font-medium text-gray-600">-</span>
                @endif
              </td>

              {{-- Status --}}
              <td class="px-6 py-4">
                @php
                  $status = $this->getUserStatus($u);
                @endphp
                @if ($status === 'Aktif')
                  <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    {{ $status }}
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                    {{ $status }}
                  </span>
                @endif
              </td>

              {{-- Last Login --}}
              <td class="px-6 py-4">
                @php
                  $last = $u->last_login_at ?? $u->updated_at ?? null;
                @endphp
                <span class="text-gray-600">
                  {{ $last ? \Illuminate\Support\Carbon::parse($last)->diffForHumans() : '-' }}
                </span>
              </td>

              {{-- Actions --}}
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button wire:click="view('{{ $u->id }}')" class="rounded-lg p-2 text-blue-600 hover:bg-blue-50 transition-colors" title="Lihat Detail">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                      <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <button wire:click="edit('{{ $u->id }}')" class="rounded-lg p-2 text-orange-600 hover:bg-orange-50 transition-colors" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
                      <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                    </svg>
                  </button>

                  <button wire:click="resetPassword('{{ $u->id }}')" class="rounded-lg p-2 text-purple-600 hover:bg-purple-50 transition-colors" title="Reset Password">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <button wire:click="delete('{{ $u->id }}')" class="rounded-lg p-2 text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                      <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center gap-3 text-gray-400">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                  </svg>
                  <p class="font-medium">Tidak ada pengguna ditemukan</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-between border-t border-gray-100 px-6 py-4">
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-500">Tampil</span>
        <select wire:model.live="perPage" class="h-9 rounded-lg border-gray-300 text-sm focus:border-red-400 focus:ring-red-400">
          @foreach ([10, 20, 30, 50] as $n)
            <option value="{{ $n }}">{{ $n }}</option>
          @endforeach
        </select>
        <span class="text-sm text-gray-500">baris</span>
      </div>
      <div>
        {{ $users->onEachSide(1)->links() }}
      </div>
    </div>
  </div>

  @include('livewire.admin.users.modals')
</div>
