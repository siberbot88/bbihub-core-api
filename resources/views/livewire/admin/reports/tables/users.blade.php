<div class="overflow-x-auto">
  <div class="mb-3 text-sm text-gray-500">Laporan Pengguna</div>
  <table class="min-w-full">
    <thead class="bg-gray-50 text-left text-sm text-gray-600">
      <tr>
        <th class="px-4 py-3">Nama</th>
        <th class="px-4 py-3">Email</th>
        <th class="px-4 py-3">Role</th>
        <th class="px-4 py-3">Status</th>
        <th class="px-4 py-3">Bergabung</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 text-sm">
      @for($i=1;$i<=10;$i++)
        <tr>
          <td class="px-4 py-3">User {{ $i }}</td>
          <td class="px-4 py-3">user{{ $i }}@mail.com</td>
          <td class="px-4 py-3">Admin</td>
          <td class="px-4 py-3"><span class="rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">Aktif</span></td>
          <td class="px-4 py-3">2025-01-0{{ $i }}</td>
        </tr>
      @endfor
    </tbody>
  </table>
</div>
