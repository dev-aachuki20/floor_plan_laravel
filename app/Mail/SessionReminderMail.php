<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SessionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sessions;

    public function __construct(Collection $sessions)
    {
        $this->sessions = $sessions;
    }

    public function build()
    {
        return $this->view('emails.session_reminder')
            ->with([
                'sessions' => $this->sessions,
            ]);
    }
}
