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
    public $subject, $rota_session_detail,$remainingRolesToConfirm;

    /**
     * Create a new message instance.
     */
    public function __construct($subject,$user,$rota_session_detail,$remainingRolesToConfirm)
    {
        $this->subject  = $subject;
        $this->user     = $user;
        $this->rota_session_detail = $rota_session_detail;
        $this->remainingRolesToConfirm = $remainingRolesToConfirm;
    }

    public function build()
    {
        return $this->markdown('emails.rota-session-closed-mail', ['user' => $this->user, 'rota_session_detail' => $this->rota_session_detail,'remainingRolesToConfirm'=>$this->remainingRolesToConfirm])->subject($this->subject);
    }
}
