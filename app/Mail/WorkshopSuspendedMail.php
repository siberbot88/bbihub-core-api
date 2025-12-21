<?php

namespace App\Mail;

use App\Models\Workshop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkshopSuspendedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Workshop $workshop,
        public string $ownerName,
        public ?string $reason = null
    ) {
    }

    public function build()
    {
        return $this->subject('Pemberitahuan: Bengkel Anda Terkena Suspend')
            ->markdown('emails.workshop.suspended', [
                'workshopName' => $this->workshop->name,
                'workshopCode' => $this->workshop->code,
                'ownerName' => $this->ownerName,
                'reason' => $this->reason,
                'adminEmail' => config('mail.admin_email', 'admin@bbihub.com'),
                'adminPhone' => config('app.admin_phone', '082143862222'),
            ]);
    }
}
