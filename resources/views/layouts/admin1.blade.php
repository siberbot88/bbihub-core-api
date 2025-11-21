<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Admin' }} – {{ config('app.name') }}</title>

  @vite(['resources/css/app.css','resources/js/app.js'])
  @livewireStyles
  @stack('styles')
</head>
<body class="min-h-screen bg-[#F4EAEA] text-neutral-900 antialiased">
  <div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="hidden lg:block w-[260px] shrink-0 bg-white border-r">
      @include('layouts.sidebar')
    </aside>

    {{-- Kolom kanan --}}
    <div class="flex-1 flex flex-col min-w-0">
      {{-- Header --}}
      <header class="sticky top-0 z-30 w-full bg-white/90 backdrop-blur border-b shadow-sm">
        <div class="px-6">
          @include('layouts.header')
        </div>
      </header>

      {{-- Konten --}}
      <main class="flex-1 overflow-y-auto bg-[#F7F7F7]">
        <div class="mx-auto w-full max-w-[1200px] px-6 py-5 space-y-4">
          @yield('content')    {{-- untuk view yang pakai @extends --}}
          {{ $slot ?? '' }}    {{-- untuk komponen Livewire yang pakai layout --}}
        </div>
      </main>
    </div>
  </div>

  @livewireScripts
  @stack('scripts')
</body>
</html>
