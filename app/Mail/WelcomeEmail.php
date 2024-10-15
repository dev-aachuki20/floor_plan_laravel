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

    public $subject;
    public $user;
    public $mfaMethod;
    public $otp;
    public $otp_expiry;
    public $setPasswordUrl;
    public $qrCodeImageUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user,  $mailData)
    {
        $this->subject    = 'Welcome Email';
        $this->user       = $user;
        $this->mfaMethod  = isset($mailData['mfaMethod']) ? $mailData['mfaMethod'] : null;
        $this->otp        = isset($mailData['otp']) ? $mailData['otp'] : null;
        $this->otp_expiry = isset($mailData['otp_expiry']) ? $mailData['otp_expiry'] : null;
        $this->setPasswordUrl = isset($mailData['setPasswordUrl']) ? $mailData['setPasswordUrl'] : null;
        $this->qrCodeImageUrl = isset($mailData['base64QRCode']) ? $mailData['base64QRCode'] : null;
    }

    public function build()
    {
        return $this->markdown('emails.auth.welcome')
            ->with([
                'user' => $this->user,
                'mfaMethod' => $this->mfaMethod,
                'setPasswordUrl' => $this->setPasswordUrl,
                'otp' => $this->otp,
                'otp_expiry' => $this->otp_expiry,
                'qrCodeImageUrl' => $this->qrCodeImageUrl,
            ])
            ->subject($this->subject);
    }
}
