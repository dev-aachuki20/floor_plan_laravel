<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RotaSessionClosedToAdmins extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subject, $hospitalName, $allSession;

    /**
     * Create a new message instance.
     */
    public function __construct($subject,$user,$hospitalName,$allSession)
    {
        $this->subject  = $subject;
        $this->user     = $user;
        $this->hospitalName = $hospitalName;
        $this->allSession = $allSession;
    }

    public function build()
    {
        return $this->markdown('emails.rota-session-closed-mail-to-admins', ['user' => $this->user, 'hospitalName'=>$this->hospitalName, 'all_session' => $this->allSession])->subject($this->subject);
    }
}
