<?php

use App\Livewire\Forms\RegisterForm;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public RegisterForm $form;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $user = $this->form->store();

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('admin.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="space-y-5">
    <!-- Header -->
    <div class="text-center space-y-2">
        <h2 class="text-3xl font-bold text-gray-900">Buat Akun Baru</h2>
        <p class="text-gray-500">Daftar sebagai superadmin BBiHub</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <!-- Name & Username Row -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Name -->
            <div class="space-y-2">
                <label for="name" class="block text-sm font-semibold text-gray-700">
                    Nama Lengkap
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input 
                        wire:model="form.name" 
                        id="name" 
                        type="text" 
                        name="name" 
                        required 
                        autofocus 
                        autocomplete="name"
                        placeholder="John Doe"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all duration-200 placeholder-gray-400 bg-gray-50 focus:bg-white text-sm"
                    />
                </div>
                @error('form.name')
                    <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Username -->
            <div class="space-y-2">
                <label for="username" class="block text-sm font-semibold text-gray-700">
                    Username
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <input 
                        wire:model="form.username" 
                        id="username" 
                        type="text" 
                        name="username" 
                        required 
                        autocomplete="username"
                        placeholder="johndoe"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all duration-200 placeholder-gray-400 bg-gray-50 focus:bg-white text-sm"
                    />
                </div>
                @error('form.username')
                    <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="block text-sm font-semibold text-gray-700">
                Email
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                <input 
                    wire:model="form.email" 
                    id="email" 
                    type="email" 
                    name="email" 
                    required 
                    autocomplete="email"
                    placeholder="nama@email.com"
                    class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all duration-200 placeholder-gray-400 bg-gray-50 focus:bg-white text-sm"
                />
            </div>
            @error('form.email')
                <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password & Confirmation Row -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Password -->
            <div class="space-y-2">
                <label for="reg-password" class="block text-sm font-semibold text-gray-700">
                    Password
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input 
                        wire:model="form.password" 
                        id="reg-password" 
                        type="password" 
                        name="password" 
                        required 
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="block w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all duration-200 placeholder-gray-400 bg-gray-50 focus:bg-white text-sm"
                    />
                    <button 
                        type="button"
                        onclick="togglePassword('reg-password', 'reg-password-icon')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-[#DC2626] transition-colors"
                    >
                        <svg id="reg-password-icon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('form.password')
                    <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">
                    Konfirmasi
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <input 
                        wire:model="form.password_confirmation" 
                        id="password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        required 
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="block w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all duration-200 placeholder-gray-400 bg-gray-50 focus:bg-white text-sm"
                    />
                    <button 
                        type="button"
                        onclick="togglePassword('password_confirmation', 'password-confirm-icon')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-[#DC2626] transition-colors"
                    >
                        <svg id="password-confirm-icon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('form.password_confirmation')
                    <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-[#DC2626] text-white font-semibold rounded-xl hover:bg-[#B91C1C] focus:outline-none focus:ring-2 focus:ring-[#DC2626] focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl mt-8"
        >
            <span>Daftar Sekarang</span>
        </button>

        <!-- Login Link -->
        <div class="text-center pt-4 border-t border-gray-200">
            <span class="text-sm text-gray-600">Sudah punya akun? </span>
            <a 
                href="{{ route('login') }}" 
                wire:navigate
                class="text-sm font-semibold text-[#DC2626] hover:text-[#B91C1C] transition-colors hover:underline"
            >
                Masuk
            </a>
        </div>
    </form>
</div>
