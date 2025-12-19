<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>
    </head>
    <body>
    <header class="border-b bg-white">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="text-lg font-semibold">BbiHub</div>
                <nav class="text-sm text-gray-600">
                    <a href="/" class="mr-4">Dashboard</a>
                </nav>
            </div>
        </div>
    </header>
        <main>
            {{ $slot }}
        </main>
    </body>
</html>
