<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .font-bubble { font-family: 'Fredoka', sans-serif; }
    </style>
</head>
<body class="antialiased bg-white text-gray-800 h-screen flex flex-col items-center justify-center p-4">
    <div class="text-center max-w-lg mx-auto">
        <!-- Illustration -->
        <!-- Error Code Bubble -->
        <div class="mb-2 flex justify-center">
            <h1 class="font-bubble text-[10rem] leading-none font-bold text-[#DC2626] drop-shadow-sm select-none">
                @yield('code')
            </h1>
        </div>

        <!-- Heading -->
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
            @yield('heading', 'Uh oh!')
        </h1>

        <!-- Message -->
        <p class="text-lg text-gray-600 mb-8 leading-relaxed">
            @yield('message')
        </p>

        <!-- Button -->
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-8 py-3 text-base font-semibold text-white transition-all duration-200 bg-[#DC2626] border border-transparent rounded-xl hover:bg-[#B91C1C] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#DC2626] shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            {{ __('Kembali ke Beranda') }}
        </a>
        
        <div class="mt-12 text-sm text-gray-400">
            &copy; {{ date('Y') }} BBiHub. All rights reserved.
        </div>
    </div>
</body>
</html>
