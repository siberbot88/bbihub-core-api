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
  @stack('styles')
</head>

<body class="bg-[#F8F6F6] text-gray-900 font-inter antialiased">
  {{-- Gunakan flex agar sidebar mengikuti tinggi konten --}}
  <div class="flex min-h-screen">
    
    {{-- Sidebar (tinggi menyesuaikan konten) --}}
    <aside class="w-64 bg-white border-r">
      @include('layouts.sidebar')
    </aside>

    {{-- Kolom kanan --}}
    <div class="flex flex-col flex-1 min-w-0">
      {{-- Header --}}
      @include('layouts.header')

      {{-- Konten utama --}}
      <main class="flex-1 bg-[#F9F9F9]">
        <div class="mx-auto w-full max-w-[1200px] px-6 py-8 space-y-6">
          @yield('content')
          {{ $slot ?? '' }}
        </div>
      </main>
    </div>
  </div>

  @livewireScripts
  @stack('scripts')
</body>
</html>
