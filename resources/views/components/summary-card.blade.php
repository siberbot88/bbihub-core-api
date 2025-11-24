@props([
  'label' => '',
  'value' => 0,
  'hint' => '',
  'icon' => '',
  'color' => 'rose', // default warna fallback
])

@php
  // Tentukan warna latar ikon berdasarkan tipe
  $colors = [
    'rose' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-600'],
    'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
    'green' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600'],
    'violet' => ['bg' => 'bg-violet-100', 'text' => 'text-violet-600'],
    'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
    'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-600'],
  ];

  $bgColor = $colors[$color]['bg'] ?? 'bg-gray-100';
  $textColor = $colors[$color]['text'] ?? 'text-gray-600';
@endphp

<div class="relative flex flex-col justify-between rounded-2xl border border-gray-100 bg-white p-5 shadow-md hover:shadow-lg transition duration-200">
  {{-- Icon di kanan atas --}}
  <div class="absolute right-4 top-4 {{ $bgColor }} p-2 rounded-xl">
    <img src="{{ asset('icons/' . $icon . '.svg') }}" alt="{{ $label }}" class="h-5 w-5 {{ $textColor }}">
  </div>

  {{-- Label dan value --}}
  <div>
    <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
    <h2 class="text-3xl font-bold text-[#E11D48] mt-2">{{ $value }}</h2>
    <p class="text-xs text-green-600 mt-1">{{ $hint }}</p>
  </div>
</div>
