<div class="overflow-x-auto">
  <div class="mb-3 text-sm text-gray-500">Laporan Kendaraan</div>
  <table class="min-w-full">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="px-4 py-3">No. Polisi</th>
        <th class="px-4 py-3">Pemilik</th>
        <th class="px-4 py-3">Tipe</th>
        <th class="px-4 py-3">Status</th>
        <th class="px-4 py-3">Aktif Terakhir</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 text-sm">
      @for($i=1;$i<=10;$i++)
        <tr>
          <td class="px-4 py-3">B {{ $i }}23 ABC</td>
          <td class="px-4 py-3">Owner {{ $i }}</td>
          <td class="px-4 py-3">Matic</td>
          <td class="px-4 py-3"><span class="rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">Aktif</span></td>
          <td class="px-4 py-3">2 hari lalu</td>
        </tr>
      @endfor
    </tbody>
  </table>
</div>
