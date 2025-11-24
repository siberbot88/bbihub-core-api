{{-- components/admin/sidebar.blade.php --}}
<div class="h-full flex flex-col">
  {{-- Brand --}}
  <div class="flex items-center gap-3 px-4 py-4 border-b">
    <img src="{{ asset('images/logo-bbihub.svg') }}" class="h-23 w-23" alt="BBI HUB" />
  </div>

  {{-- Menu --}}
  <nav class="flex-1 overflow-y-auto py-3">
    @php
      $items = [
        ['label' => 'Dashboard',          'icon' => 'dashboard',   'route' => route('admin.dashboard')],
        ['label' => 'Manajemen Pengguna', 'icon' => 'user',        'route' => route('admin.users')],
        ['label' => 'Manajemen Bengkel',  'icon' => 'workshop',    'route' => route('admin.workshops')],
        ['label' => 'Manajemen Promosi',  'icon' => 'promo',       'route' => route('admin.promotions')],
        ['label' => 'Data Center',        'icon' => 'datacenter',  'route' => route('admin.data-center')],
        ['label' => 'Laporan',            'icon' => 'report',      'route' => route('admin.reports')],
        ['label' => 'Pengaturan',         'icon' => 'setting',     'route' => route('admin.settings')],
      ];
      $current = url()->current();
    @endphp

    <ul class="space-y-1 px-2">
      @foreach($items as $it)
        @php $active = $current === $it['route']; @endphp
        <li>
          <a href="{{ $it['route'] }}"
             class="group flex items-center gap-3 rounded-xl px-3 py-2
                    {{ $active ? 'bg-rose-50 text-rose-600' : 'text-gray-700 hover:bg-gray-50' }}">
            <x-svg :name="$it['icon']" class="h-5 w-5 {{ $active ? 'text-rose-600' : 'text-gray-400 group-hover:text-gray-600' }}" />
            <span class="text-sm font-medium">{{ $it['label'] }}</span>
          </a>
        </li>
      @endforeach
    </ul>
  </nav>
</div>
