<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MfaGoogleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name, $qrCodeImageUrl;

    public function __construct($name,$qrCodeImageUrl)
    {
        $this->name  = $name;
        $this->qrCodeImageUrl = $qrCodeImageUrl;
    }

    public function build()
    {
        return $this->markdown('emails.auth.mfa_google', [
            'name'  => $this->name,
            'otp'   => $this->qrCodeImageUrl,
        ])->subject('Resend Google Authenticator QR Code');
    }
}
