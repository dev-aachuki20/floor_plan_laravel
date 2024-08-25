<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RotaSessionClosedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subject, $rota_session_detail;

    /**
     * Create a new message instance.
     */
    public function __construct($subject,$user,$rota_session_detail)
    {
        $this->subject  = $subject;
        $this->user     = $user;
        $this->rota_session_detail = $rota_session_detail;
    }

    public function build()
    {
        return $this->markdown('emails.rota-session-closed-mail', ['user' => $this->user, 'rota_session_detail' => $this->rota_session_detail])->subject($this->subject);
    }
}
