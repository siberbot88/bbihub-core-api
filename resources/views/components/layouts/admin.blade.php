<!-- <!DOCTYPE html>
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
      <x-admin.sidebar />
    </aside>

    {{-- Kolom kanan --}}
    <div class="flex-1 flex flex-col min-w-0 justify-between">

      {{-- Header --}}
      <header class="sticky top-0 z-30 w-full bg-white/90 backdrop-blur border-b shadow-sm">
        <div class="w-full px-6">
          <x-admin.header />
        </div>
      </header>

      {{-- Konten --}}
      <main class="flex-1 overflow-y-auto bg-[#F7F7F7]">
        <div class="mx-auto w-full max-w-[1200px] px-[24px] py-[20px] space-y-[16px]">
          {{ $slot }}
        </div>
      </main>

      {{-- Footer --}}
      <footer class="w-full border-t bg-white py-4">
        <div class="max-w-[1200px] mx-auto px-6 flex items-center">
          <img src="{{ asset('images/logo-bbihub.svg') }}" class="h-8 w-8" alt="">
          <div class="ml-3 leading-tight">
            <span class="text-lg font-bold">BBI HUB</span><br>
            <span class="text-red-600 font-semibold">Plus</span>
          </div>
        </div>
      </footer>

    </div>
  </div>

  @livewireScripts
  @stack('scripts')
</body>
</html> -->
