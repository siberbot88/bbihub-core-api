<div class="h-full flex flex-col bg-white border-r shadow-sm">
  {{-- Brand --}}
  <div class="flex items-center gap-3 px-4 py-4 border-b">
    <img src="{{ asset('images/logo-bbihub.svg') }}" class="h-25 w-auto" alt="BBI HUB" />
  </div>

  {{-- Menu --}}
  <nav class="flex-1 overflow-y-auto py-3">
    @php
      $items = [
        ['label' => 'Dashboard',          'icon' => 'dashboard',   'route' => 'admin.dashboard'],
        ['label' => 'Manajemen Pengguna', 'icon' => 'user',        'route' => 'admin.users'],
        ['label' => 'Manajemen Bengkel',  'icon' => 'workshop',    'route' => 'admin.workshops'],
        ['label' => 'Manajemen Promosi',  'icon' => 'promo',       'route' => 'admin.promotions'],
        ['label' => 'Data Center',        'icon' => 'datacenter',  'route' => 'admin.data-center'],
        ['label' => 'Laporan',            'icon' => 'report',      'route' => 'admin.reports'],
        ['label' => 'Pengaturan',         'icon' => 'settings',    'route' => 'admin.settings'],
      ];
    @endphp

    <ul class="space-y-1 px-2">
      @foreach($items as $it)
        @php
          $isActive = request()->routeIs($it['route']);
        @endphp

        <li>
          <a href="{{ route($it['route']) }}"
             class="relative flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-150
             {{ $isActive 
                ? 'bg-[#FFF1F2] text-[#E11D48] font-semibold shadow-[0_2px_6px_rgba(225,29,72,0.15)]' 
                : 'text-gray-700 hover:bg-gray-50 hover:text-[#E11D48]' }}">
            
            {{-- Garis vertikal merah di kiri --}}
            @if($isActive)
              <span class="absolute left-0 top-1/2 -translate-y-1/2 h-6 w-[4px] bg-[#E11D48] rounded-full"></span>
            @endif

            <img 
              src="{{ asset('icons/' . ($isActive ? $it['icon'] . '-red.svg' : $it['icon'] . '.svg')) }}" 
              alt="{{ $it['label'] }}" 
              class="h-5 w-5 transition-transform duration-200 group-hover:scale-110"
            >

            {{-- teks aktif merah --}}
            <span class="text-sm leading-tight {{ $isActive ? 'text-[#E11D48]' : '' }}">
              {{ $it['label'] }}
            </span>
          </a>
        </li>
      @endforeach
    </ul>
  </nav>
</div>
