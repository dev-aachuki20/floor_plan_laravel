<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MfaTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name, $token, $tokenExpire;

    public function __construct($name,$token,$tokenExpire)
    {
        $this->name  = $name;
        $this->token = $token;
        $this->tokenExpire = $tokenExpire;
    }

    public function build()
    {
        return $this->markdown('emails.auth.mfa_token', [
            'name'  => $this->name,
            'token' => $this->token,
            'tokenExpire' => $this->tokenExpire
        ])->subject('Verify Your Account');
    }
}
