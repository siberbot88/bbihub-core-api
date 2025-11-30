<div class="h-full flex flex-col bg-white border-r border-gray-200 shadow-sm">
    {{-- Brand --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100">
        <img src="{{ asset('images/logo-bbih.svg') }}" class="h-12 w-auto" alt="BBI HUB" />
        <div class="flex flex-col">
            <div class="text-xl font-bold leading-none text-gray-900">
                BBI HUB
            </div>
            <div class="text-xl font-bold leading-none text-[#DC2626]">
                Plus
            </div>
            <div class="text-[10px] font-medium text-gray-500 mt-1">
                Super Admin Dashboard
            </div>
        </div>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 overflow-y-auto py-6 px-4">
        @php
            $items = [
                [
                    'label' => 'Dashboard',
                    'route' => 'admin.dashboard',
                    'icon_path' => 'M3 13h1v7c0 1.105.895 2 2 2h12c1.105 0 2-.895 2-2v-7h1a1 1 0 0 0 .707-1.707l-9-9a1 1 0 0 0-1.414 0l-9 9A1 1 0 0 0 3 13zm7 7v-5h4v5h-4z' // Home (Solid-ish for simplicity, or I can use outline path)
                ],
                [
                    'label' => 'Manajemen Pengguna',
                    'route' => 'admin.users.index',
                    'icon_path' => 'M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M23 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75 M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z' // User Group (Feather/Heroicon style)
                ],
                // Let's use standard Heroicons paths (Outline usually, but user asked for color change)
                // I will use specific paths below in the loop logic to keep it clean
            ];
            
            // Redefining items with proper Heroicons paths
            // Using Outline for default, and we can fill them or stroke them based on state
            $menuItems = [
                [
                    'label' => 'Dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'home'
                ],
                [
                    'label' => 'Manajemen Pengguna',
                    'route' => 'admin.users.index',
                    'icon' => 'users'
                ],
                [
                    'label' => 'Manajemen Bengkel',
                    'route' => 'admin.workshops',
                    'icon' => 'wrench'
                ],
                [
                    'label' => 'Manajemen Promosi',
                    'route' => 'admin.promotions',
                    'icon' => 'megaphone'
                ],
                [
                    'label' => 'Data Center',
                    'route' => 'admin.data-center',
                    'icon' => 'server'
                ],
                [
                    'label' => 'Laporan',
                    'route' => 'admin.reports',
                    'icon' => 'chart'
                ],
                [
                    'label' => 'Pengaturan',
                    'route' => 'admin.settings',
                    'icon' => 'cog'
                ],
            ];
        @endphp

        <ul class="space-y-2">
            @foreach($menuItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                    $activeClass = $isActive 
                        ? 'bg-red-50 text-[#DC2626]' 
                        : 'text-gray-600 hover:bg-gray-50 hover:text-[#DC2626]';
                    $iconClass = $isActive ? 'text-[#DC2626]' : 'text-gray-400 group-hover:text-[#DC2626]';
                @endphp

                <li>
                    <a href="{{ route($item['route']) }}" wire:navigate
                       class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 {{ $activeClass }}">
                        
                        {{-- Icon --}}
                        <svg class="h-6 w-6 transition-colors duration-200 {{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            @if($item['icon'] === 'home')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            @elseif($item['icon'] === 'users')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            @elseif($item['icon'] === 'wrench')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            @elseif($item['icon'] === 'megaphone')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.467a23.849 23.849 0 010 3.467m0-3.467a23.849 23.849 0 000 3.467" />
                            @elseif($item['icon'] === 'server')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                            @elseif($item['icon'] === 'chart')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            @elseif($item['icon'] === 'cog')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            @endif
                        </svg>

                        {{-- Label --}}
                        <span class="font-medium text-sm">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</div>
