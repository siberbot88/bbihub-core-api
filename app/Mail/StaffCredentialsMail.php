<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $username,
        public string $plainPassword,
        public string $loginUrl
    ) {}

    public function build()
    {
        return $this->subject('Akun Staff Bengkel Anda')
            ->markdown('emails.staff.credentials', [
                'name'     => $this->recipientName,
                'username' => $this->username,
                'password' => $this->plainPassword,
                'loginUrl' => $this->loginUrl,
            ]);
    }
}
