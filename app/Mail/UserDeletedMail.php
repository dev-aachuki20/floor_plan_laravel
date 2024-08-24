<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserDeletedMail extends Mailable implements ShouldQueue 
{
    use Queueable, SerializesModels;

    public $subject, $user, $authUser;
  
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $user, $authUser)
    {
        $this->subject  = $subject;
        $this->user = $user;
        $this->authUser = $authUser;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user-deleted-mail', [
                'user'  => $this->user,
                'authUser' => $this->authUser
            ])->subject($this->subject);
    }
}

