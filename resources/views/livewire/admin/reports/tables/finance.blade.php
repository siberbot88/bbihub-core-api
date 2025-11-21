<div class="overflow-x-auto">
  <div class="mb-3 text-sm text-gray-500">Laporan Keuangan</div>
  <table class="min-w-full">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="px-4 py-3">Tanggal</th>
        <th class="px-4 py-3">Order</th>
        <th class="px-4 py-3">Metode</th>
        <th class="px-4 py-3">Subtotal</th>
        <th class="px-4 py-3">Biaya</th>
        <th class="px-4 py-3">Total</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 text-sm">
      @for($i=1;$i<=10;$i++)
        <tr>
          <td class="px-4 py-3">2025-10-{{ sprintf('%02d',$i) }}</td>
          <td class="px-4 py-3">ORD-00{{ $i }}</td>
          <td class="px-4 py-3">QRIS</td>
          <td class="px-4 py-3">Rp 500.000</td>
          <td class="px-4 py-3">Rp 5.000</td>
          <td class="px-4 py-3 font-medium">Rp 495.000</td>
        </tr>
      @endfor
    </tbody>
  </table>
</div>
