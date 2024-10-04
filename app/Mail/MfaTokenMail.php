<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MfaTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name, $otp, $otpExpire;

    public function __construct($name,$otp,$otpExpire)
    {
        $this->name  = $name;
        $this->otp = $otp;
        $this->otpExpire = $otpExpire;
    }

    public function build()
    {
        return $this->markdown('emails.auth.mfa_token', [
            'name'  => $this->name,
            'otp'   => $this->otp,
            'otpExpire' => $this->otpExpire
        ])->subject('Verify Your Account');
    }
}
