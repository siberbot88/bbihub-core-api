<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>
    {{ $title ?? (View::hasSection('title') ? yieldContent('title') : 'Dashboard') }} – {{ config('app.name') }}
  </title>

  {{-- Asset & Style --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
  <link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" />
  <style>
    /* Custom NProgress Color */
    #nprogress .bar {
      background: #DC2626 !important; /* BBiHub Red */
      height: 3px !important;
    }
    #nprogress .peg {
      box-shadow: 0 0 10px #DC2626, 0 0 5px #DC2626 !important;
    }
    #nprogress .spinner-icon {
      border-top-color: #DC2626 !important;
      border-left-color: #DC2626 !important;
    }
  </style>
  @stack('styles')
</head>

<body class="bg-[#F8F6F6] text-gray-900 font-inter antialiased" x-data="{ sidebarOpen: false }">
  {{-- Gunakan flex agar sidebar mengikuti tinggi konten --}}
  <div class="flex min-h-screen">
    
    {{-- Mobile Sidebar Overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80 z-40 lg:hidden" style="display: none;"></div>

    {{-- Sidebar (Fixed on mobile AND desktop) --}}
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:fixed lg:inset-y-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
      @include('layouts.sidebar')
    </aside>

    {{-- Kolom kanan --}}
    <div class="flex flex-col flex-1 min-w-0 lg:pl-64 transition-all duration-300">
      {{-- Header --}}
      @include('layouts.header')

      {{-- Konten utama --}}
      <main class="flex-1 bg-[#F9F9F9]">
        <div class="mx-auto w-full max-w-[1200px] px-6 py-8 space-y-6">
          @yield('content')
          {{ $slot ?? '' }}
        </div>
      </main>

      {{-- Footer --}}
      @include('layouts.footer')
    </div>
  </div>

  @livewireScripts
  <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
  <script>
    document.addEventListener('livewire:navigating', () => {
        NProgress.start();
    });

    document.addEventListener('livewire:navigated', () => {
        NProgress.done();
    });
  </script>
  @stack('scripts')
</body>
</html>
