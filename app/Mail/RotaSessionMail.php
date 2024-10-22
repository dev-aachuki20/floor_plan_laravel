<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RotaSessionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subject, $rota_session_detail;
    public $notificationType;
   
    /**
     * Create a new message instance.
     */
    public function __construct($subject,$user,$rota_session_detail,$notificationType)
    {
        $this->subject  = $subject;
        $this->user     = $user;
        $this->rota_session_detail = $rota_session_detail;
        $this->notificationType = $notificationType;
    }

    public function build()
    {
        if($this->notificationType == 'final_reminder'){
            return $this->markdown('emails.final-session-mail', ['user' => $this->user, 'rota_session_detail' => $this->rota_session_detail])->subject($this->subject);
        }

        return $this->markdown('emails.rota-session-mail', ['user' => $this->user, 'rota_session_detail' => $this->rota_session_detail])->subject($this->subject);
    }
}
