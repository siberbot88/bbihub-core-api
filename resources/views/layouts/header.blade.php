{{-- layouts/header.blade.php --}}
<div class="w-full px-6 py-3 flex items-center justify-between gap-3 bg-white">
  {{-- Search --}}
  <div class="relative flex-1">
   <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
      <img src="{{ asset('icons/search.svg') }}" alt="Search" class="h-5 w-5" />
    </span>
    <input type="search" placeholder="Cari..." class="w-full rounded-full border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-28 text-sm outline-none placeholder:text-gray-400 focus:bg-white focus:border-gray-300" />
    <button class="absolute right-1 top-1/2 -translate-y-1/2 rounded-full bg-rose-600 px-4 py-1.5 text-sm font-semibold text-white shadow hover:bg-rose-700">Search</button>
  </div>

  {{-- Right icons --}}
  <div class="ml-2 flex items-center gap-2">
    <button class="rounded-full p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-700" title="Notifikasi">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M14.25 18.5a2.25 2.25 0 1 1-4.5 0h4.5Z"/><path d="M5 10.75a7 7 0 1 1 14 0v3.318l1.106 1.658A1 1 0 0 1 19.25 17H4.75a1 1 0 0 1-.856-1.574L5 14.068v-3.318Z"/></svg>
    </button>
    <button class="rounded-full p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-700" title="Pengaturan">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M11.983 2a2 2 0 0 1 1.965 1.63l.166.94a6.99 6.99 0 0 1 1.582.915l.9-.328a2 2 0 0 1 2.44 1.006l.94 1.865a2 2 0 0 1-.475 2.364l-.7.622c.043.305.065.616.065.931s-.022.626-.065.93l.7.622a2 2 0 0 1 .475 2.365l-.94 1.864a2 2 0 0 1-2.44 1.006l-.9-.327a6.99 6.99 0 0 1-1.582.914l-.166.94a2 2 0 0 1-1.965 1.631h-1.886a2 2 0 0 1-1.965-1.63l-.166-.94a6.99 6.99 0 0 1-1.582-.915l-.9.328a2 2 0 0 1-2.44-1.006l-.94-1.865a2 2 0 0 1 .475-2.364Z"/></svg>
    </button>
    <div class="h-8 w-8 overflow-hidden rounded-full ring-2 ring-rose-100">
      <img src="{{ asset('images/avatar.png') }}" alt="avatar" class="h-full w-full object-cover">
    </div>
  </div>
</div>
