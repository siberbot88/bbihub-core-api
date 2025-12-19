# 📖 Membership Concepts Clarification

Dokumen ini dibuat untuk mengklarifikasi perbedaan antara dua konsep "Membership" yang ada di dalam ekosistem BBI HUB. Perbedaan ini penting dipahami untuk menghindari kebingungan saat pengembangan fitur maupun saat menjelaskan produk.

## 🔑 Ringkasan Perbedaan

| Fitur | **Owner Subscription (System A)** | **Customer Loyalty Membership (System B)** |
| :--- | :--- | :--- |
| **Model Bisnis** | **B2B** (Business to Business) | **B2B2C** (Business to Business to Consumer) |
| **Siapa yang Bayar?** | **Mitra Bengkel** (Owner) | **Pelanggan Bengkel** (End Customer) |
| **Ke Siapa Bayarnya?** | Ke **Platform BBI HUB** | Ke **Mitra Bengkel** (via BBI HUB) |
| **Tujuan** | Unlock fitur aplikasi (Pro/Premium) | Program loyalitas pelanggan bengkel |
| **Entities (Code)** | `OwnerSubscription`, `SubscriptionPlan` | `Membership` (Tier), `CustomerMembership` |
| **Contoh Level** | Basic, Pro, Enterprise | Bronze, Silver, Gold, Platinum |

---

## 1. Owner Subscription (Langganan Mitra)
**"Mitra Bengkel berlangganan ke BBI HUB"**

Ini adalah revenue stream utama untuk kita (BBI HUB). Mitra bengkel membayar biaya bulanan/tahunan untuk mendapatkan akses penuh ke fitur aplikasi manajemen bengkel.

### Poin Penting:
*   **Target User:** Pemilik Bengkel.
*   **Mekanisme:** Pemilik bengkel memilih paket (misal: "Pro Plan") di menu "Upgrade".
*   **Benefit:**
    *   Unlimited jumlah pegawai.
    *   Laporan keuangan detail.
    *   Akses fitur **Customer Loyalty Membership** (System B).
*   **Penanggung Jawab:** BBI HUB (Kita).

---

## 2. Customer Loyalty Membership (Member Pelanggan)
**"Pelanggan berlangganan ke Bengkel Tertentu"**

Ini adalah **fitur** yang kita sediakan agar Mitra Bengkel bisa membangun basis pelanggan setia mereka sendiri. BBI HUB hanya menyediakan *platform/tools*-nya.

### Poin Penting:
*   **Target User:** Orang yang punya motor/mobil (Pelanggan bengkel).
*   **Mekanisme:**
    1.  Mitra Bengkel membuat Tier Member (misal: "Gold Member Rp 50.000/tahun").
    2.  Pelanggan membeli membership tersebut lewat aplikasi BBI HUB.
    3.  Uang masuk ke Mitra Bengkel (via Wallet/Midtrans split).
*   **Benefit (Yang menentukan Mitra Bengkel):**
    *   Diskon jasa servis 10%.
    *   Prioritas antrian booking.
    *   Gratis cuci motor 1x sebulan.
    *   Poin reward setiap transaksi.
*   **Penanggung Jawab:** Mitra Bengkel masing-masing.

---

## 💡 Contoh Skenario

**Pak Budi (Pemilik Bengkel "Maju Jaya"):**
1.  Pak Budi membayar **Owner Subscription** (Rp 100.000/bulan) ke BBI HUB agar bisa pakai aplikasi secara *full*.
2.  Di dalam aplikasi, Pak Budi mengaktifkan fitur *Loyalty*.
3.  Pak Budi membuat program "Member VIP Maju Jaya".
4.  **Si Andi (Pelanggan)** datang servis motor.
5.  Si Andi tertarik jadi member VIP, lalu membayar Rp 50.000 ke Bengkel Maju Jaya.
6.  Si Andi sekarang jadi **Customer Member** di Bengkel Maju Jaya dan dapat diskon servis.

---

> **Catatan Teknis:**
> Saat ini (Fase 1), kita sedang fokus menyelesaikan implementasi **Owner Subscription** agar platform bisa mulai menghasilkan revenue. **Customer Loyalty Membership** adalah fitur lanjutan (Next Phase) untuk menambah nilai jual aplikasi kita ke Mitra Bengkel.
