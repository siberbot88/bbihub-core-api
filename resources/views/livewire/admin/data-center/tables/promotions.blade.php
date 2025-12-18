<div>
  <div class="flex items-center justify-between border-b px-4 py-3 text-sm">
    <div class="font-medium">Data Promosi</div>
    <div class="text-neutral-500">Total: {{ number_format($rows?->total() ?? 0) }} Promosi</div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-neutral-200 text-sm">
      <thead class="bg-neutral-50">
        <tr class="text-neutral-600">
          <th class="px-4 py-3 text-left w-10"><input type="checkbox" class="rounded border-neutral-300"></th>
          <th class="px-4 py-3 text-left">Judul</th>
          <th class="px-4 py-3 text-left">Kode</th>
          <th class="px-4 py-3 text-left">Diskon</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-left">Masa Berlaku</th>
          <th class="px-4 py-3 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100">
        @forelse($rows as $p)
          <tr class="hover:bg-neutral-50" data-id="{{ $p->id }}">
            <td class="px-4 py-3"><input type="checkbox" class="row-checkbox rounded border-neutral-300" value="{{ $p->id }}"></td>
            <td class="px-4 py-3">
              <div class="font-medium">{{ $p->title }}</div>
              <div class="text-xs text-neutral-500">{{ Str::limit($p->description ?? '-', 60) }}</div>
            </td>
            <td class="px-4 py-3">{{ $p->code ?? '-' }}</td>
            <td class="px-4 py-3">{{ $p->discount ? ($p->discount . (isset($p->is_percentage) && $p->is_percentage ? '%' : '')) : '-' }}</td>
            <td class="px-4 py-3">
              @php $map=['active'=>'bg-emerald-100 text-emerald-700','pending'=>'bg-yellow-100 text-yellow-700','expired'=>'bg-rose-100 text-rose-700']; @endphp
              <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $map[$p->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($p->status ?? 'unknown') }}</span>
            </td>
            <td class="px-4 py-3">{{ ($p->starts_at ?? '-') . ' — ' . ($p->ends_at ?? '-') }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button wire:click="deleteRow('{{ $p->id }}')" class="rounded-md bg-rose-50 border border-rose-200 px-3 py-1 text-sm text-rose-600 hover:bg-rose-100">🗑 Hapus</button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-4 py-6 text-center text-neutral-500">Tidak ada data promosi.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="border-t px-4 py-3">
    {{ $rows->onEachSide(1)->links() }}
  </div>
</div>
