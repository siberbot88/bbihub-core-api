@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
@endphp

{{-- layouts/header.blade.php --}}
<header class="w-full px-4 md:px-8 py-4 flex items-center justify-between gap-4 md:gap-6 bg-white border-b border-gray-100 sticky top-0 z-40">
  
  {{-- Mobile Menu Button --}}
  <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-gray-500 hover:bg-gray-100 rounded-lg">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
    </svg>
  </button>

  {{-- Search Bar --}}
  <div class="relative flex-1 max-w-2xl">
    <div class="relative group">
        <span class="absolute inset-y-0 left-4 flex items-center text-gray-400 group-focus-within:text-[#DC2626] transition-colors">
            {{-- Heroicon: MagnifyingGlass --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </span>
        <input type="text" 
               placeholder="Cari sesuatu..." 
               class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-12 pr-32 text-sm text-gray-700 outline-none placeholder:text-gray-400 focus:bg-white focus:border-[#DC2626] focus:ring-1 focus:ring-[#DC2626] transition-all duration-200" />
        <button class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-xl bg-[#DC2626] px-5 py-2 text-xs font-bold text-white shadow-md shadow-red-200 hover:bg-[#B91C1C] hover:shadow-lg transition-all duration-200">
            Search
        </button>
    </div>
  </div>

  {{-- Right Actions --}}
  <div class="flex items-center gap-4">
    {{-- Notifications --}}
    <button class="relative rounded-xl p-2.5 text-gray-500 hover:bg-red-50 hover:text-[#DC2626] transition-colors duration-200 group" title="Notifikasi">
      {{-- Heroicon: Bell --}}
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
      </svg>
      {{-- Notification Dot --}}
      <span class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-[#DC2626] ring-2 ring-white"></span>
    </button>

    {{-- Divider --}}
    <div class="h-8 w-px bg-gray-200"></div>

    {{-- User Profile --}}
    <div class="relative" x-data="{ open: false }">
      <button @click="open = !open" class="flex items-center gap-3 rounded-xl p-1 pr-3 hover:bg-gray-50 transition-colors duration-200 focus:outline-none">
        {{-- Avatar --}}
        <div class="h-10 w-10 overflow-hidden rounded-full ring-2 ring-gray-100 group-hover:ring-[#DC2626] transition-all">
            @if(Auth::user()->photo)
              <img src="{{ Storage::url(Auth::user()->photo) }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
            @else
              <div class="h-full w-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center">
                <span class="text-sm font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
              </div>
            @endif
        </div>
        
        {{-- Name & Role --}}
        <div class="hidden md:block text-left">
            <p class="text-sm font-bold text-gray-900 leading-none">{{ Auth::user()->name }}</p>
            <p class="text-[10px] font-medium text-gray-500 mt-0.5">Super Admin</p>
        </div>

        {{-- Chevron --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" 
             class="w-4 h-4 text-gray-400 transition-transform duration-200"
             :class="{'rotate-180': open}">
          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
      </button>

      {{-- Dropdown --}}
      <div x-show="open" 
           @click.away="open = false"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0 translate-y-2"
           x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="opacity-100 translate-y-0"
           x-transition:leave-end="opacity-0 translate-y-2"
           class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl py-2 z-50 border border-gray-100 ring-1 ring-black ring-opacity-5"
           style="display: none;">
        
        <div class="px-5 py-4 border-b border-gray-50 mb-1">
            <p class="text-sm font-bold text-gray-900">{{ Auth::user()->name }}</p>
            <p class="text-xs text-gray-500 truncate mt-0.5">{{ Auth::user()->email }}</p>
        </div>

        <div class="px-2 space-y-1 py-1">
            <a href="{{ route('admin.profile') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 rounded-xl hover:bg-gray-50 hover:text-[#DC2626] transition-colors group">
                <div class="p-1.5 rounded-lg bg-gray-100 text-gray-500 group-hover:bg-red-50 group-hover:text-[#DC2626] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
                Profile Saya
            </a>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-red-600 rounded-xl hover:bg-red-50 transition-colors text-left group">
                    <div class="p-1.5 rounded-lg bg-red-50 text-red-500 group-hover:bg-red-100 group-hover:text-red-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                        </svg>
                    </div>
                    Log Out
                </button>
            </form>
        </div>
      </div>
    </div>
  </div>
</header>
