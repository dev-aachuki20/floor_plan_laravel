<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $mfaMethod, $setPasswordUrl, $otp,$otp_expiry,$qrCodeImageUrl;
    /**
     * Create a new message instance.
     */
    public function __construct($user,  $mfaMethod, $setPasswordUrl,$otp=null,$otp_expiry=null,$qrCodeImageUrl=null)
    {
        $this->subject   = 'Welcome Email';
        $this->user      = $user;
        $this->mfaMethod = $mfaMethod;
        $this->otp       = $otp;
        $this->otp_expiry = $otp_expiry;
        $this->setPasswordUrl = $setPasswordUrl;
        $this->qrCodeImageUrl = $qrCodeImageUrl;
    }

    public function build()
    {
        return $this->markdown('emails.auth.welcome', ['user' => $this->user, 'mfaMethod' => $this->mfaMethod,'setPasswordUrl'=>$this->setPasswordUrl,'otp'=>$this->otp, 'otp_expiry'=>$otp_expiry,'qrCodeImageUrl' => $this->qrCodeImageUrl])->subject($this->subject);
    }
}
