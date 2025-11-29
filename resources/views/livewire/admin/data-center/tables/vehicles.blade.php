<div class="overflow-x-auto">
  <div class="flex items-center justify-between px-4 pt-4 text-sm text-gray-500">
    <div>Data Kendaraan</div>
    <div>Total: {{ $rows?->total() ?? 0 }} Kendaraan</div>
  </div>
  <table class="min-w-full">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300"></th>
        <th class="px-4 py-3">NOMOR POLISI</th>
        <th class="px-4 py-3">PEMILIK</th>
        <th class="px-4 py-3">MEREK / TIPE</th>
        <th class="px-4 py-3">STATUS</th>
        <th class="px-4 py-3">AKTIF TERAKHIR</th>
        <th class="px-4 py-3 text-left">AKSI</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 text-sm">
      @forelse($rows as $v)
        <tr class="hover:bg-gray-50/60">
          <td class="px-4 py-4"><input type="checkbox" class="rounded border-gray-300"></td>
          <td class="px-4 py-4 font-medium text-gray-900">{{ $v->plate_number }}</td>
          <td class="px-4 py-4">{{ $v->owner_name }}</td>
          <td class="px-4 py-4">{{ $v->brand }} / {{ $v->type }}</td>
          <td class="px-4 py-4">
            @php $map=['active'=>'bg-emerald-100 text-emerald-700','inactive'=>'bg-rose-100 text-rose-700','pending'=>'bg-yellow-100 text-yellow-700']; @endphp
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $map[$v->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($v->status) }}</span>
          </td>
          <td class="px-4 py-4 text-gray-500">{{ $v->last_active_at ? \Illuminate\Support\Carbon::parse($v->last_active_at)->diffForHumans() : '-' }}</td>
          <td class="px-4 py-4">
            <div class="flex justify-end gap-3">
              <a href="#" title="Edit">✏️</a>
              <button class="text-rose-600" title="Hapus">🗑</button>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Tidak ada data.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="flex items-center justify-between p-4 text-sm text-gray-500">
    <div>Menampilkan {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() ?? 0 }} hasil</div>
    <div>{{ $rows->onEachSide(1)->links() }}</div>
  </div>
</div>
