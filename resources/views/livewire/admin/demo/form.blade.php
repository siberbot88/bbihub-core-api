<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Form Demo Servis</h1>
            <p class="text-sm text-gray-500">Simulasi alur servis dari penerimaan hingga pembayaran</p>
        </div>
        <div>
            <button wire:click="resetForm"
                class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Buat Demo Baru
            </button>
        </div>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div class="rounded-lg bg-green-50 p-4 text-green-800 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ session('message') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Stepper --}}
    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="relative flex items-center justify-between">
            <div class="absolute left-0 top-1/2 -z-10 h-0.5 w-full bg-gray-200"></div>

            {{-- Steps: Logic to hide Payment step if not active --}}
            @php
                $steps = [
                    1 => ['label' => 'Customer', 'icon' => 'user'],
                    2 => ['label' => 'Kendaraan', 'icon' => 'truck'],
                    3 => ['label' => 'Servis', 'icon' => 'wrench-screwdriver'],
                ];
                // Only show Step 4 if we are currently IN step 4 (Payment Mode)
                if ($step === 4) {
                    $steps[4] = ['label' => 'Pembayaran (Kasir)', 'icon' => 'credit-card'];
                }

                // Show Step 5 if in Feedback Mode
                if ($step === 5) {
                    // Keep step 4 visible to show progress
                    $steps[4] = ['label' => 'Pembayaran', 'icon' => 'credit-card'];
                    $steps[5] = ['label' => 'Ulasan', 'icon' => 'star'];
                }
            @endphp

            @foreach($steps as $s => $info)
                <div class="flex flex-col items-center bg-white px-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 
                                        {{ $step >= $s ? 'border-red-600 bg-red-600' : 'border-gray-300 bg-white' }} 
                                        transition-colors">
                        {{-- Raw SVG to ensure color control --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-5 w-5 {{ $step >= $s ? 'text-white' : 'text-gray-400' }}">
                            @if($info['icon'] === 'user')
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            @elseif($info['icon'] === 'truck')
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            @elseif($info['icon'] === 'wrench-screwdriver')
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                            @elseif($info['icon'] === 'credit-card')
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            @elseif($info['icon'] === 'star')
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.563.045.797.77.364 1.145l-4.232 3.655a.563.563 0 00-.18.59l1.28 5.48a.562.562 0 01-.84.61L12 18.068l-4.904 2.875a.562.562 0 01-.84-.61l1.28-5.48a.563.563 0 00-.18-.59L3.093 10.542c-.433-.375-.199-1.1.364-1.145l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            @endif
                        </svg>
                    </div>
                    <span class="mt-2 text-xs font-medium {{ $step >= $s ? 'text-red-600' : 'text-gray-500' }}">
                        {{ $info['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Form Content --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Main Form --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">

                {{-- STEP 1: CUSTOMER --}}
                @if($step === 1)
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Data Pelanggan</h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" wire:model="customerName"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Contoh: Budi Santoso">
                                @error('customerName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nomor Telepon (WA)</label>
                                <input type="text" wire:model="customerPhone"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="08123456789">
                                @error('customerPhone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email (Opsional)</label>
                                <input type="email" wire:model="customerEmail"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="budi@example.com">
                                @error('customerEmail') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Alamat</label>
                                <textarea wire:model="customerAddress" rows="2"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- STEP 2: VEHICLE --}}
                @if($step === 2)
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Data Kendaraan</h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nomor Polisi</label>
                                <input type="text" wire:model="vehiclePlate"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="B 1234 ABC">
                                @error('vehiclePlate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jenis Kendaraan</label>
                                <select wire:model="vehicleType"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="motor">Motor</option>
                                    <option value="mobil">Mobil</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Merk (Brand)</label>
                                <input type="text" wire:model="vehicleBrand"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Honda, Toyota, Yamaha">
                                @error('vehicleBrand') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Model</label>
                                <input type="text" wire:model="vehicleModel"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Vario 150, Avanza">
                                @error('vehicleModel') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tahun</label>
                                <input type="number" wire:model="vehicleYear"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="2020">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Warna</label>
                                <input type="text" wire:model="vehicleColor"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Hitam, Putih">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- STEP 3: SERVICE --}}
                @if($step === 3)
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Data Servis</h3>
                        <div class="grid gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pilih Bengkel</label>
                                <select wire:model="workshopId"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    @foreach($workshops as $ws)
                                        <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                                    @endforeach
                                </select>
                                @error('workshopId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Keluhan / Judul Servis</label>
                                <input type="text" wire:model="serviceName"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Ganti Oli & Servis Ringan">
                                @error('serviceName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Deskripsi (Opsional)</label>
                                <textarea wire:model="serviceDesc" rows="3"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Detail pekerjaan..."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estimasi Biaya (Rp)</label>
                                <input type="number" wire:model="servicePrice"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="0">
                                @error('servicePrice') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                @endif

                {{-- STEP 4: PAYMENT --}}
                @if($step === 4)
                    <div class="space-y-6">
                        <div class="text-center">
                            <h3 class="text-lg font-bold text-gray-900">Konfirmasi Pembayaran</h3>
                            <p class="text-sm text-gray-500">Silakan cek detail transaksi sebelum melakukan pembayaran.</p>
                        </div>

                        {{-- Summary Box --}}
                        <div class="rounded-lg bg-gray-50 p-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pelanggan</span>
                                <span class="font-medium">{{ $customer->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kendaraan</span>
                                <span class="font-medium">{{ $vehicle->name ?? '-' }}
                                    ({{ $vehicle->plate_number ?? '-' }})</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Servis</span>
                                <span class="font-medium">{{ $service->name ?? '-' }}</span>
                            </div>
                            <hr class="border-gray-200 my-2">
                            <div class="flex justify-between text-base font-semibold">
                                <span>Total Tagihan</span>
                                <span>Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Voucher --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700">Kode Voucher</label>
                            <div class="flex gap-2">
                                <input type="text" wire:model="voucherCode"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Masukkan kode promo">
                                <button type="button" wire:click="checkVoucher"
                                    class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                                    Gunakan
                                </button>
                            </div>
                            @if($voucherMessage)
                                <span class="text-xs text-green-600 font-medium">{{ $voucherMessage }}</span>
                            @endif
                            @error('voucherCode') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- Final Amount --}}
                        <div class="flex items-center justify-between rounded-lg bg-red-50 p-4 border border-red-100">
                            <span class="text-red-700 font-medium">Total Bayar</span>
                            <span class="text-xl font-bold text-red-700">Rp
                                {{ number_format($finalAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif

                {{-- STEP 5: FEEDBACK --}}
                @if($step === 5)
                    <div class="space-y-6 text-center">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Bagaimana Pelayanan Kami?</h3>
                            <p class="text-sm text-gray-500">Berikan penilaian untuk membantu kami menjadi lebih baik.</p>
                        </div>

                        {{-- Star Rating --}}
                        <div class="flex justify-center gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" wire:click="$set('rating', {{ $i }})"
                                    class="transition-transform hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="h-10 w-10 {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-200' }}">
                                        <path fill-rule="evenodd"
                                            d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endfor
                        </div>
                        @error('rating') <span class="block text-sm text-red-600">{{ $message }}</span> @enderror

                        {{-- Comment --}}
                        <div class="text-left">
                            <label class="block text-sm font-medium text-gray-700">Komentar / Saran (Opsional)</label>
                            <textarea wire:model="feedbackComment" rows="4"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                placeholder="Pelayanan sangat memuaskan..."></textarea>
                            @error('feedbackComment') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endif

                {{-- Button Navigation --}}
                <div class="mt-8 flex justify-between border-t pt-6">
                    @if($step > 1 && $step < 4) {{-- Hide Back on Step 4 (Payment) if opened via table --}}
                        <button wire:click="prevStep" type="button"
                            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-4 focus:ring-gray-200">
                            Kembali
                        </button>
                    @elseif($step === 4)
                        <button wire:click="resetForm" type="button"
                            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal / Tutup
                        </button>
                    @else
                        <div></div> {{-- Spacer --}}
                    @endif

                    @if($step < 3)
                        <button wire:click="nextStep" type="button"
                            class="flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700 focus:ring-4 focus:ring-red-300">
                            Selanjutnya
                            <x-svg name="arrow-right" class="h-4 w-4" />
                        </button>
                    @elseif($step === 3)
                        <button wire:click="nextStep" type="button"
                            class="flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700 focus:ring-4 focus:ring-red-300">
                            {{-- Step 3 Action: Save to Queue --}}
                            Simpan ke Antrian
                            <x-svg name="check" class="h-4 w-4" />
                        </button>
                    @elseif($step === 4)
                        {{-- Pay Button --}}
                        <div x-data>
                            <button wire:click="processPayment" wire:loading.attr="disabled" type="button"
                                class="flex items-center gap-2 rounded-lg bg-green-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-green-700 focus:ring-4 focus:ring-green-300 disabled:opacity-50">
                                <span>Bayar Sekarang</span>
                                <x-svg name="check" class="h-4 w-4" />
                            </button>
                        </div>
                    @elseif($step === 5)
                        <button wire:click="resetForm" type="button" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Lewati
                        </button>
                        <button wire:click="submitFeedback" type="button" class="flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700 focus:ring-4 focus:ring-red-300">
                             Kirim Ulasan
                        </button>
                    @endif
                </div>

            </div>
        </div>

        {{-- Sidebar Info --}}
        <div class="lg:col-span-1">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h4 class="text-base font-semibold text-gray-900 mb-4">Ringkasan Demo</h4>

                <ol class="relative border-l border-gray-200">
                    <li class="mb-6 ml-4">
                        <div
                            class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white {{ $step > 1 || $customer ? 'bg-green-500' : 'bg-gray-200' }}">
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Pelanggan</h3>
                        <p class="text-xs text-gray-500">{{ $customer->name ?? 'Belum diisi' }}</p>
                    </li>
                    <li class="mb-6 ml-4">
                        <div
                            class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white {{ $step > 2 || $vehicle ? 'bg-green-500' : 'bg-gray-200' }}">
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Kendaraan</h3>
                        <p class="text-xs text-gray-500">{{ $vehicle->plate_number ?? 'Belum diisi' }}</p>
                    </li>
                    <li class="mb-6 ml-4">
                        <div
                            class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white {{ $step > 3 || $service ? 'bg-green-500' : 'bg-gray-200' }}">
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Servis</h3>
                        <p class="text-xs text-gray-500">{{ $service->name ?? 'Belum diisi' }}</p>
                    </li>
                    <li class="ml-4">
                        <div
                            class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white {{ $step === 4 && $snapToken ? 'bg-green-500' : 'bg-gray-200' }}">
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Pembayaran</h3>
                        <p class="text-xs text-gray-500">{{ $transaction ? 'Menunggu Pembayaran' : '-' }}</p>
                    </li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Recent Services Table --}}
    <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden mt-8">
        <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
            <h3 class="font-semibold text-gray-900">Daftar Demo Terakhir & Antrian</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Pelanggan</th>
                        <th class="px-6 py-3">Kendaraan</th>
                        <th class="px-6 py-3">Servis/Bengkel</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 border-t border-gray-100 bg-white">
                    @forelse($recentServices as $srv)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $srv->created_at->format('d M H:i') }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $srv->customer->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $srv->vehicle->plate_number ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $srv->name }}</div>
                                <div class="text-xs text-gray-500">{{ $srv->workshop->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($srv->transaction?->status === 'paid')
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        Lunas
                                    </span>
                                @else
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="inline-flex w-fit items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            Servis: {{ ucfirst($srv->status) }}
                                        </span>
                                        <span
                                            class="inline-flex w-fit items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                            Bayar: {{ ucfirst($srv->transaction?->status ?? 'pending') }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($srv->status === 'pending')
                                    <button wire:click="markAsComplete('{{ $srv->id }}')"
                                        class="inline-flex items-center gap-1 rounded bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" class="h-3 w-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Selesaikan
                                    </button>
                                @elseif($srv->status === 'completed' && $srv->transaction?->status !== 'paid')
                                    <button wire:click="openPayment('{{ $srv->transaction?->id }}')"
                                        class="inline-flex items-center gap-1 rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" class="h-3 w-3">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                        </svg>
                                        Bayar
                                    </button>
                                @elseif($srv->transaction?->status === 'paid' && !$srv->transaction->feedback)
                                    <button wire:click="openFeedback('{{ $srv->transaction?->id }}')"
                                        class="inline-flex items-center gap-1 rounded bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-yellow-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="h-3 w-3">
                                            <path fill-rule="evenodd"
                                                d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Beri Ulasan
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data visualisasi demo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MIDTRANS SCRIPT --}}
    @section('scripts')
        <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('services.midtrans.client_key') ?? env('MIDTRANS_CLIENT_KEY') }}"></script>
    @endsection

    {{-- LIVEWIRE SCRIPT FOR SNAP --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('snap-token-generated', (event) => {
                const token = event[0]['token']; // Livewire dispatch sends array
                console.log("Snap Token:", token);

                window.snap.pay(token, {
                    onSuccess: function (result) {
                        console.log('success', result);
                        @this.call('finishDemo');
                        alert("Pembayaran Berhasil!");
                    },
                    onPending: function (result) {
                        console.log('pending', result);
                        alert("Pembayaran Pending");
                    },
                    onError: function (result) {
                        console.log('error', result);
                        alert("Pembayaran Gagal");
                    },
                    onClose: function () {
                        console.log('customer closed the popup without finishing the payment');
                    }
                });
            });
        });
    </script>
</div>