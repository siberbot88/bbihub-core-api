@component('mail::message')
    # Selamat datang, {{ $name }}

    Akun staff Anda untuk {{ config('app.name') }} telah berhasil dibuat.
    Anda sekarang dapat login menggunakan kredensial di bawah ini.

    @component('mail::panel')
        Berikut adalah detail login Anda:

        **Username:** `{{ $username }}`
        **Password Sementara:** `{{ $password }}`
    @endcomponent

    Silakan klik tombol di bawah untuk membuka aplikasi dan login.

    @component('mail::button', ['url' => $loginUrl])
        Buka Aplikasi
    @endcomponent

    > **Penting:** Demi keamanan, Anda akan diminta mengganti password pada login pertama kali.

    Terima kasih,<br>
    {{ config('app.name') }}

    {{-- Ini adalah bagian Footer untuk info keamanan --}}
    @component('mail::subcopy')
        Kami di **BBI HUB Official** berkomitmen penuh untuk menjaga kerahasiaan dan keamanan data Anda.
        Informasi ini bersifat rahasia, dibuat secara otomatis oleh sistem, dan tidak untuk dibagikan.
    @endcomponent
@endcomponent
