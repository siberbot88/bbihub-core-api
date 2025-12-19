<div class="overflow-x-auto">
  {{-- Header --}}
  <div class="flex items-center justify-between px-4 pt-4 text-sm text-gray-500">
    <div>Data Bengkel</div>
    <div>Total: {{ $rows?->total() ?? 0 }} Bengkel</div>
  </div>

  {{-- Table --}}
  <table class="min-w-full divide-y divide-gray-200 text-sm">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="w-10 px-4 py-3">
          <input type="checkbox" class="rounded border-gray-300">
        </th>
        <th class="px-4 py-3">BENGKEL</th>
        <th class="px-4 py-3">STATUS</th>
        <th class="px-4 py-3">LOKASI</th>
        <th class="px-4 py-3">RATING</th>
        <th class="px-4 py-3">BERGABUNG</th>
        <th class="px-4 py-3 text-right">AKSI</th>
      </tr>
    </thead>

    <tbody class="divide-y divide-gray-100">
      @forelse($rows as $w)
        <tr class="hover:bg-gray-50/60">

          {{-- Checkbox per item --}}
          <td class="px-4 py-4">
            <input
              type="checkbox"
              class="rounded border-gray-300"
              {{-- jika mau multi-select: wire:model="selected" value="{{ $w->id }}" --}}
            >
          </td>

          {{-- Nama Bengkel --}}
          <td class="px-4 py-4">
            <div class="font-medium text-gray-900">{{ $w->name }}</div>
            <div class="text-xs text-gray-500">ID: {{ $w->code }}</div>
          </td>

          {{-- Status --}}
          <td class="px-4 py-4">
            @php
              $map = [
                'active'    => 'bg-emerald-100 text-emerald-700',
                'pending'   => 'bg-yellow-100 text-yellow-700',
                'suspended' => 'bg-rose-100 text-rose-700',
              ];
            @endphp

            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $map[$w->status] ?? 'bg-gray-100 text-gray-700' }}">
              {{ ucfirst($w->status) }}
            </span>
          </td>

          {{-- Lokasi --}}
          <td class="px-4 py-4">
            {{ $w->city }}
          </td>

          {{-- Rating --}}
          <td class="px-4 py-4">
            <div class="inline-flex items-center gap-1">
              <span>⭐</span>
              <span class="font-medium">
                {{ $w->rating ? number_format($w->rating, 1) : '-' }}
              </span>
            </div>
          </td>

          {{-- Tanggal Bergabung --}}
          <td class="px-4 py-4 text-gray-500">
            {{ $w->joined_at
                ? \Illuminate\Support\Carbon::parse($w->joined_at)->diffForHumans()
                : '-' }}
          </td>

          {{-- Aksi --}}
          <td class="px-4 py-4">
            <div class="flex justify-end gap-3">

              {{-- Tombol Edit --}}
              <button
                type="button"
                wire:click="editWorkshop({{ $w->id }})"
                class="hover:text-gray-800"
                title="Edit"
              >
                ✏️
              </button>

              {{-- Tombol Hapus --}}
              <button
                type="button"
                wire:click="deleteWorkshop({{ $w->id }})"
                onclick="return confirm('Yakin ingin menghapus bengkel ini?')"
                class="text-rose-600 hover:text-rose-700"
                title="Hapus"
              >
                🗑
              </button>

            </div>
          </td>

        </tr>
      @empty
        <tr>
          <td colspan="7" class="px-4 py-10 text-center text-gray-500">
            Tidak ada data.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- Footer pagination --}}
  <div class="flex items-center justify-between p-4 text-sm text-gray-500">
    <div>
      Menampilkan {{ $rows->firstItem() ?? 0 }}
      - {{ $rows->lastItem() ?? 0 }}
      dari {{ $rows->total() ?? 0 }} hasil
    </div>

    <div>
      {{ $rows->onEachSide(1)->links() }}
    </div>
  </div>
</div>
