<div class="overflow-x-auto">
  <div class="mb-3 text-sm text-gray-500">Laporan Bengkel</div>
  <table class="min-w-full">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="px-4 py-3">Bengkel</th>
        <th class="px-4 py-3">Kota</th>
        <th class="px-4 py-3">Transaksi</th>
        <th class="px-4 py-3">Pendapatan</th>
        <th class="px-4 py-3">Rating</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 text-sm">
      @for($i=1;$i<=10;$i++)
        <tr>
          <td class="px-4 py-3">Bengkel {{ $i }}</td>
          <td class="px-4 py-3">Jakarta</td>
          <td class="px-4 py-3">120</td>
          <td class="px-4 py-3">Rp 3.500.000</td>
          <td class="px-4 py-3">4.8</td>
        </tr>
      @endfor
    </tbody>
  </table>
</div>
