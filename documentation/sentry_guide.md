# Panduan Penggunaan Sentry untuk BBI HUB

BBI HUB sekarang telah terintegrasi dengan **Sentry**, sebuah platform monitoring error dan performa aplikasi. Dokumen ini menjelaskan cara memanfaatkannya.

## 1. Bagaimana Sentry Bekerja?
Anda **tidak perlu melakukan apa-apa** secara teknis. Sentry berjalan otomatis di background (di sisi Backend).
- Setiap kali terjadi **Error 500** (Server Error) atau **Exception** yang tidak tertangani, Sentry akan:
  1.  Menangkap detail error (pesan error, baris kode, user yang mengalami, IP address).
  2.  Mengirim notifikasi ke Email Anda.
  3.  Mencatatnya di Dashboard Sentry.

## 2. Dashboard Sentry
Silakan login ke [sentry.io](https://sentry.io/) untuk melihat detail:

### A. Menu "Issues" (Masalah)
Ini adalah menu utama yang paling sering Anda gunakan.
- **Daftar Error**: Berisi list semua error yang terjadi. Error yang sama akan dikelompokkan menjadi satu "Issue".
- **Detail Issue**: Klik salah satu judul error untuk melihat:
    - **Stack Trace**: Menunjukkan di file mana baris kode yang rusak.
    - **Breadcrumbs**: Jejak aktivitas user sebelum error terjadi (misal: buka halaman -> klik tombol -> error).
    - **User Info**: Siapa user yang mengalami error ini.

### B. Menu "Performance"
- Melihat endpoint API mana yang paling sering diakses (`Throughput`).
- Melihat endpoint mana yang paling lambat (`Duration`).
- Membantu Anda memutuskan bagian mana yang perlu dioptimasi.

## 3. Workflow Penanganan Error
Ketika Anda menerima notifikasi email dari Sentry:
1.  **Klik Link** di email ("View on Sentry").
2.  Lihat **Stack Trace** untuk mengetahui penyebabnya.
3.  Perbaiki kode di Backend.
4.  Setelah kode diperbaiki dan di-deploy, klik tombol **"Resolve"** di dashboard Sentry.
    - Ini akan "menutup" isu tersebut. Jika error yang sama muncul lagi, Sentry akan membuat isu baru (Regresion).

## 4. Konfigurasi Lanjutan (Opsional)
- **Alerts**: Anda bisa mengatur agar Sentry mengirim notifikasi ke WhatsApp atau Slack (butuh integrasi tambahan) jika ingin respon lebih cepat.
- **Environment**: Saat ini Sentry mencatat semua error. Nanti saat Production, kita bisa memfilter agar error Development tidak tercampur.
