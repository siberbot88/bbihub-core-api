<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            /* Custom animations */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeInLeft {
                from {
                    opacity: 0;
                    transform: translateX(-30px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            @keyframes scaleIn {
                from {
                    opacity: 0;
                    transform: scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .animate-fade-in-up {
                animation: fadeInUp 0.8s ease-out;
            }

            .animate-fade-in-left {
                animation: fadeInLeft 0.8s ease-out;
            }

            .animate-scale-in {
                animation: scaleIn 0.6s ease-out;
            }

            /* Workshop background side - NO overlay, pure image */
            .workshop-side {
                position: relative;
                background-image: url('{{ asset('images/workshop_img.png') }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }

            /* Dark overlay for better text contrast on image */
            .workshop-side::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.4) 100%);
                z-index: 1;
            }

            .workshop-content {
                position: relative;
                z-index: 2;
            }

            /* Form card with subtle shadow */
            .form-card {
                background: white;
                border-radius: 24px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), 0 0 1px rgba(0, 0, 0, 0.1);
            }

            /* Hover effects for feature items */
            .feature-item {
                transition: all 0.3s ease;
            }

            .feature-item:hover {
                transform: translateX(8px);
            }

            .feature-item:hover .feature-icon {
                transform: scale(1.1);
                box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
            }

            .feature-icon {
                transition: all 0.3s ease;
            }
        </style>
    </head>
    <body class="font-poppins antialiased">
        <!-- Fullscreen Workshop Background -->
        <div class="min-h-screen relative workshop-side">
            <!-- Main Content Container -->
            <div class="min-h-screen flex items-center justify-between p-8 lg:p-12 workshop-content">
                <!-- Left Side - Branding & Features (Hidden on Mobile) -->
                <div class="hidden lg:block lg:w-5/12 xl:w-1/2">
                    <div class="max-w-lg space-y-8">
                        <!-- Main Title -->
                        <div class="space-y-4 animate-fade-in-left">
                            <h1 class="text-5xl font-bold text-white leading-tight">
                                Welcome to<br>
                                <span class="text-[#DC2626]">BBiHub Dashboard</span>
                            </h1>
                            <p class="text-xl text-gray-200">
                                Master data management untuk aplikasi mobile bengkel Anda.
                            </p>
                        </div>

                        <!-- Features -->
                        <div class="space-y-4 pt-8">
                            <div class="feature-item flex items-start space-x-4 group">
                                <div class="feature-icon flex-shrink-0 w-12 h-12 rounded-xl bg-[#DC2626] flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white text-lg">Centralized Management</h3>
                                    <p class="text-gray-300 text-sm">Kelola semua data master aplikasi dari satu tempat</p>
                                </div>
                            </div>

                            <div class="feature-item flex items-start space-x-4 group">
                                <div class="feature-icon flex-shrink-0 w-12 h-12 rounded-xl bg-[#DC2626] flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white text-lg">Superadmin Access</h3>
                                    <p class="text-gray-300 text-sm">Akses eksklusif untuk administrator sistem</p>
                                </div>
                            </div>

                            <div class="feature-item flex items-start space-x-4 group">
                                <div class="feature-icon flex-shrink-0 w-12 h-12 rounded-xl bg-[#DC2626] flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white text-lg">Real-time Sync</h3>
                                    <p class="text-gray-300 text-sm">Sinkronisasi langsung dengan aplikasi mobile</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Floating Form Card -->
                <div class="w-full lg:w-auto lg:ml-auto lg:mr-8 xl:mr-16">
                    <div class="w-full lg:w-[480px] xl:w-[520px]">
                        <!-- Mobile Logo -->
                        <div class="lg:hidden mb-8 text-center animate-fade-in-up">
                            <h2 class="text-4xl font-bold text-white">
                                <span class="text-[#DC2626]">BBi</span><span class="text-white">Hub</span>
                            </h2>
                        </div>

                        <!-- Form Card -->
                        <div class="form-card px-10 py-10 animate-scale-in">
                            {{ $slot }}
                        </div>

                        <!-- Footer -->
                        <p class="mt-6 text-center text-sm text-gray-300">
                            &copy; {{ date('Y') }} BBiHub. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Toggle Script -->
        <script>
            function togglePassword(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    `;
                } else {
                    input.type = 'password';
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    `;
                }
            }
        </script>
    </body>
</html>
