# Workshop Update Feature & Daily Log (2025-12-14)

## Overview
Hari ini fokus pada perbaikan dan peningkatan fitur **Edit Workshop** (sebelumnya User Profile) di aplikasi Mobile dan penyesuaian Backend API untuk mendukungnya. Selain itu, perbaikan juga dilakukan pada sistem akses fitur Premium.

## 1. Backend Changes (Laravel)

### A. Workshop Model (`app/Models/Workshop.php`)
- **Fix**: Menghapus casting `'operational_days' => 'array'`.
  - *Masalah*: Data dikirim dari mobile sebagai string sederhana (e.g., "Senin - Jumat"), namun backend mencoba meng-cast ke Array sehingga menyebabkan error saat validasi atau penyimpanan.
  - *Solusi*: Default ke string.

### B. Route & Controller (`api.php` & `WorkshopApiController`)
- Menggunakan endpoint `PUT /owners/workshops/{id}`.
- Controller sudah mendukung upload file `photo` via `MultipartRequest`.

### C. Validation (`UpdateWorkshopRequest.php`)
- Melonggarkan validasi untuk field opsional.
- Menambahkan validasi untuk field baru:
  - `maps_url` (nullable|url)
  - `operational_days` (nullable|string)
  - `address`, `phone`, `email`, dll.

## 2. Mobile Changes (Flutter)

### A. Edit Workshop UI (`EditProfilePage.dart`)
- **Refactoring**: Mengubah halaman dari edit "User Profile" menjadi "Edit Workshop".
- **Fields Baru**: Menambahkan input untuk:
  - Google Maps URL
  - Jam Operasional (Buka/Tutup)
  - Status Bengkel (Switch Buka/Tutup)
  - Foto Workshop (Image Picker)
- **Logic**: Mengimplementasikan `ApiService.updateWorkshop` untuk menyimpan data secara real ke server, menggantikan simulasi mock.

### B. API Integration (`ApiService.dart`)
- **Method Update**: Memperbarui signature `updateWorkshop` untuk menerima semua parameter profil bengkel.
- **Multipart Request**: Menggunakan `http.MultipartRequest` dan metode spoofing `_method: PUT` agar diproses dengan benar oleh Laravel sebagai Update Request yang menyertakan file.

### C. Model (`Workshop.dart`)
- **Localhost Sanitization**: Web server backend mengembalikan URL gambar dengan `localhost` (karena environment dev lokal).
- **Fix**: Menambahkan helper di `fromJson` untuk mendeteksi `localhost` dan mengubahnya menjadi `10.0.2.2` agar **Android Emulator** dapat memuat gambar tersebut.

### D. Auth State (`AuthProvider.dart`)
- Memperbaiki `checkLoginStatus()` agar memuat ulang data user dan relasi `workshop` setelah update berhasil, sehingga perubahan foto/nama langsung terlihat di UI tanpa perlu logout.

## 3. Bug Fixes

### A. Premium Access Locked
- **Masalah**: Fitur Laporan & Analitik tetap terkunci meski user sudah beli paket.
- **Penyebab**: Penggunaan `context.read<AuthProvider>()` di widget build tidak mendengarkan perubahan state.
- **Solusi**: Mengganti menjadi `context.watch<AuthProvider>()` dan menambahkan logika auto-fetch data saat status premium terdeteksi.

### B. Operational Days Error
- **Error**: "The operational days field must be a string".
- **Fix**: Sinkronisasi tipe data antara Mobile (String), Request Validation (String), dan Model Cast (None/String).

### C. Foto Tidak Muncul
- **Error**: Foto berhasil diupload tapi tidak muncul di profil.
- **Penyebab**: Emulator tidak bisa akses URL `http://localhost:8000/...`.
- **Fix**: Auto-replace `localhost` ke `10.0.2.2` di sisi Mobile Model.

## Next Steps
- Verifikasi alur "Laporan Analitik" dengan data real.
- Testing fitur upload dokumen legalitas (jika belum).
