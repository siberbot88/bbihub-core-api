<div class="overflow-x-auto">
  {{-- Header tabel --}}
  <div class="flex items-center justify-between px-4 pt-4 text-sm text-gray-500">
    <div>Data Kendaraan</div>
    <div>Total: {{ $rows?->total() ?? 0 }} Kendaraan</div>
  </div>

  {{-- Tabel --}}
  <table class="min-w-full divide-y divide-gray-200 text-sm">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="w-10 px-4 py-3">
          <input type="checkbox" class="rounded border-gray-300">
          {{-- kalau mau "select all", nanti bisa pakai wire:model dll --}}
        </th>
        <th class="px-4 py-3">NOMOR POLISI</th>
        <th class="px-4 py-3">PEMILIK</th>
        <th class="px-4 py-3">MEREK / TIPE</th>
        <th class="px-4 py-3">STATUS</th>
        <th class="px-4 py-3">AKTIF TERAKHIR</th>
        <th class="px-4 py-3 text-right">AKSI</th>
      </tr>
    </thead>

    <tbody class="divide-y divide-gray-100">
      @forelse($rows as $v)
        <tr class="hover:bg-gray-50/60">
          {{-- Checkbox per baris --}}
          <td class="px-4 py-4">
            <input
              type="checkbox"
              class="rounded border-gray-300"
              {{-- kalau mau multi-select: wire:model="selected" value="{{ $v->id }}" --}}
            >
          </td>

          {{-- Nomor polisi --}}
          <td class="px-4 py-4 font-medium text-gray-900">
            {{ $v->plate_number }}
          </td>

          {{-- Pemilik --}}
          <td class="px-4 py-4">
            {{ $v->owner_name }}
          </td>

          {{-- Merek / Tipe --}}
          <td class="px-4 py-4">
            {{ $v->brand }} / {{ $v->type }}
          </td>

          {{-- Status --}}
          <td class="px-4 py-4">
            @php
              $map = [
                'active'   => 'bg-emerald-100 text-emerald-700',
                'inactive' => 'bg-rose-100 text-rose-700',
                'pending'  => 'bg-yellow-100 text-yellow-700',
              ];
            @endphp
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $map[$v->status] ?? 'bg-gray-100 text-gray-700' }}">
              {{ ucfirst($v->status) }}
            </span>
          </td>

          {{-- Aktif terakhir --}}
          <td class="px-4 py-4 text-gray-500">
            {{ $v->last_active_at
                ? \Illuminate\Support\Carbon::parse($v->last_active_at)->diffForHumans()
                : '-' }}
          </td>

          {{-- Aksi --}}
          <td class="px-4 py-4">
            <div class="flex justify-end gap-3">
              <button
                type="button"
                {{-- pastikan ada method editVehicle($id) di component kalau mau dipakai --}}
                wire:click="editVehicle({{ $v->id }})"
                class="text-gray-500 hover:text-gray-800"
                title="Edit"
              >
                ✏️
              </button>

              <button
                type="button"
                {{-- pastikan ada method deleteVehicle($id) di component --}}
                wire:click="deleteVehicle({{ $v->id }})"
                onclick="return confirm('Yakin ingin menghapus kendaraan ini?')"
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
