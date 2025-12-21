@component('mail::message')
# Pemberitahuan Penting: Bengkel Anda Terkena Suspend

Halo **{{ $ownerName }}**,

Kami informasikan bahwa bengkel Anda telah di-suspend oleh tim admin kami.

## Detail Bengkel
- **Nama Bengkel**: {{ $workshopName }}
- **Kode Bengkel**: {{ $workshopCode }}
@if($reason)
    - **Alasan**: {{ $reason }}
@endif

---

## Apa yang harus dilakukan?

Bengkel yang ter-suspend tidak dapat menerima pesanan baru dan tidak akan muncul dalam pencarian pelanggan.

Untuk mengaktifkan kembali bengkel Anda, **mohon segera menghubungi tim admin kami** melalui:

@component('mail::panel')
📧 Email: **{{ $adminEmail }}**
📱 WhatsApp: **{{ $adminPhone }}**
@endcomponent

@component('mail::button', ['url' => config('app.url'), 'color' => 'error'])
Login ke Dashboard
@endcomponent

Tim admin kami akan membantu Anda menyelesaikan masalah ini secepatnya.

Terima kasih atas pengertian Anda.

Salam,
{{ config('app.name') }}
@endcomponent