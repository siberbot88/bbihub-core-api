<?php

use App\Livewire\Forms\UpdateProfileInformationForm;
use App\Livewire\Forms\UpdatePasswordForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public UpdateProfileInformationForm $profileForm;
    public UpdatePasswordForm $passwordForm;

    public $photoPreview = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->profileForm->setUser($user);
        $this->passwordForm->setUser($user);
    }

    /**
     * Update profile information.
     */
    public function updateProfileInformation(): void
    {
        $this->profileForm->update();

        $this->dispatch('profile-updated');
        $this->redirectRoute('admin.profile');
    }

    /**
     * Update password.
     */
    public function updatePassword(): void
    {
        $this->passwordForm->update();

        $this->dispatch('password-updated');
        session()->flash('status', 'password-updated');
    }

    /**
     * Remove photo.
     */
    public function removePhoto(): void
    {
        $this->profileForm->removePhoto = true;
        $this->profileForm->update();
        $this->photoPreview = null;

        $this->dispatch('profile-updated');
        $this->redirectRoute('admin.profile');
    }

    /**
     * Updated photo preview.
     */
    public function updatedProfileFormPhoto(): void
    {
        $this->photoPreview = $this->profileForm->photo?->temporaryUrl();
    }
}; ?>

<div class="space-y-8">
    <!-- Page Header -->
    <div class="border-b border-gray-200 pb-6">
        <h1 class="text-3xl font-bold text-gray-900">Profil Saya</h1>
        <p class="mt-2 text-gray-500">Kelola informasi profil dan keamanan akun Anda</p>
    </div>

    <!-- Success Messages -->
    @if (session('status') === 'profile-information-updated')
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-green-700">Profil berhasil diperbarui!</p>
            </div>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-green-700">Password berhasil diubah!</p>
            </div>
        </div>
    @endif

    <!-- Profile Information Section -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Informasi Profil</h2>

        <form wire:submit="updateProfileInformation" class="space-y-6">
            <!-- Photo Upload -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Foto Profil</label>
                <div class="flex items-center gap-6">
                    <!-- Current Photo Display -->
                    <div class="relative">
                        @if ($photoPreview)
                            <img src="{{ $photoPreview }}" alt="Preview"
                                class="w-24 h-24 rounded-full object-cover ring-4 ring-gray-100">
                        @elseif (Auth::user()->photo)
                            <img src="{{ Storage::url(Auth::user()->photo) }}" alt="{{ Auth::user()->name }}"
                                class="w-24 h-24 rounded-full object-cover ring-4 ring-gray-100">
                        @else
                            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center ring-4 ring-gray-100">
                                <span class="text-3xl font-bold text-white">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Upload Controls -->
                    <div class="flex-1 space-y-3">
                        <div class="flex gap-3">
                            <label
                                class="px-4 py-2 bg-[#DC2626] text-white text-sm font-semibold rounded-lg hover:bg-[#B91C1C] transition-colors cursor-pointer inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Pilih Foto</span>
                                <span wire:loading wire:target="profileForm.photo" class="text-xs">(Uploading...)</span>
                                <input type="file" wire:model="profileForm.photo" accept="image/*" class="hidden">
                            </label>

                            @if (Auth::user()->photo || $photoPreview)
                                <button type="button" wire:click="removePhoto"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                                    Hapus Foto
                                </button>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500">JPG, PNG atau GIF. Maksimal 2MB.</p>

                        @error('profileForm.photo')
                            <p class="text-sm text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Name & Username -->
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Lengkap
                    </label>
                    <input wire:model="profileForm.name" id="name" type="text"
                        class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all bg-gray-50 focus:bg-white">
                    @error('profileForm.name')
                        <p class="mt-1 text-sm text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                        Username
                    </label>
                    <input wire:model="profileForm.username" id="username" type="text"
                        class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all bg-gray-50 focus:bg-white">
                    @error('profileForm.username')
                        <p class="mt-1 text-sm text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    Email
                </label>
                <input wire:model="profileForm.email" id="email" type="email"
                    class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all bg-gray-50 focus:bg-white">
                @error('profileForm.email')
                    <p class="mt-1 text-sm text-[#DC2626]">{{ $message }}</p>
                @enderror
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-3 bg-[#DC2626] text-white font-semibold rounded-xl hover:bg-[#B91C1C] focus:outline-none focus:ring-2 focus:ring-[#DC2626] focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Password Update Section -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-2">Ubah Password</h2>
        <p class="text-sm text-gray-500 mb-6">Pastikan akun Anda menggunakan password yang kuat untuk keamanan.</p>

        <form wire:submit="updatePassword" class="space-y-6">
            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                    Password Saat Ini
                </label>
                <div class="relative">
                    <input wire:model="passwordForm.current_password" id="current_password" type="password"
                        class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all bg-gray-50 focus:bg-white">
                    <button type="button" onclick="togglePassword('current_password', 'current-icon')"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[#DC2626]">
                        <svg id="current-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                @error('passwordForm.current_password')
                    <p class="mt-1 text-sm text-[#DC2626]">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Password Baru
                    </label>
                    <div class="relative">
                        <input wire:model="passwordForm.password" id="password" type="password"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all bg-gray-50 focus:bg-white">
                        <button type="button" onclick="togglePassword('password', 'password-icon')"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[#DC2626]">
                            <svg id="password-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('passwordForm.password')
                        <p class="mt-1 text-sm text-[#DC2626]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                        Konfirmasi Password
                    </label>
                    <div class="relative">
                        <input wire:model="passwordForm.password_confirmation" id="password_confirmation" type="password"
                            class="block w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#DC2626] focus:border-transparent transition-all bg-gray-50 focus:bg-white">
                        <button type="button" onclick="togglePassword('password_confirmation', 'confirm-icon')"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[#DC2626]">
                            <svg id="confirm-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Update Password Button -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-3 bg-[#DC2626] text-white font-semibold rounded-xl hover:bg-[#B91C1C] focus:outline-none focus:ring-2 focus:ring-[#DC2626] focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    Ubah Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }
</script>
