<div>
  <div class="flex items-center justify-between border-b px-4 py-3 text-sm">
    <div class="font-medium">Data Pengguna</div>
    <div class="text-neutral-500">
      Total: {{ number_format($rows?->total() ?? 0) }} Pengguna
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-neutral-200 text-sm">
      <thead class="bg-neutral-50">
        <tr class="text-neutral-600">
          <th class="w-10 px-4 py-3 text-center">
            {{-- Checkbox "Select All" --}}
            <input
              type="checkbox"
              class="rounded border-neutral-300"
              wire:click="toggleSelectAll"
              @checked($rows && isset($selected) && count($selected) === $rows->count())
            >
          </th>
          <th class="px-4 py-3 text-left">Pengguna</th>
          <th class="px-4 py-3 text-left">Role</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-left">Login Terakhir</th>
          <th class="px-4 py-3 text-left">Aksi</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-neutral-100">
        @forelse($rows as $u)
          <tr class="hover:bg-neutral-50">
            {{-- Checkbox per user --}}
            <td class="px-4 py-3 text-center">
              <input
                type="checkbox"
                class="rounded border-neutral-300"
                wire:model="selected"
                value="{{ $u->id }}"
              >
            </td>

            {{-- Info pengguna --}}
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-full bg-neutral-200 text-sm font-semibold text-neutral-700">
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
              @if(method_exists($u, 'getRoleNames'))
                @foreach($u->getRoleNames() as $r)
                  <span class="me-1 rounded-md bg-violet-100 px-2 py-0.5 text-xs text-violet-700">
                    {{ $r }}
                  </span>
                @endforeach
              @else
                <span class="rounded-md bg-neutral-100 px-2 py-0.5 text-xs text-neutral-600">-</span>
              @endif
            </td>

            {{-- Status --}}
            <td class="px-4 py-3">
              @php
                $active = isset($u->status)
                  ? $u->status === 'active'
                  : (bool) ($u->email_verified_at ?? false);
              @endphp
              <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                {{ $active ? 'AKTIF' : 'Nonaktif' }}
              </span>
            </td>

            {{-- Login terakhir --}}
            <td class="px-4 py-3">
              {{ ($u->last_login_at ?? $u->updated_at)
                    ? \Illuminate\Support\Carbon::parse($u->last_login_at ?? $u->updated_at)->diffForHumans()
                    : '-' }}
            </td>

            {{-- Aksi --}}
            <td class="px-4 py-3">
              <button
                type="button"
                wire:click='detail("{{ $u->id }}")'
                class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors"
              >
                Detail
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-neutral-500">
              Tidak ada data.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="border-t px-4 py-3">
    {{ $rows->onEachSide(1)->links() }}
  </div>
</div>
