<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name,$subject;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$url,$subject)
    {
        
        $this->name = ucwords($name);
        $this->url = $url;
        $this->subject = $subject;

    }

   /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this->markdown('emails.auth.verify-email', [
                'name' => $this->name,
                'url' => $this->url,
            ])->subject($this->subject);
    }
}
