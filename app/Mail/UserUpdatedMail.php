<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserUpdatedMail extends Mailable implements ShouldQueue 
{
    use Queueable, SerializesModels;

    public $subject, $user, $updatedFields;
  
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $user, $updatedFields)
    {
        $this->subject  = $subject;
        $this->user = $user;
        $this->updatedFields = $updatedFields;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user-updated-mail', [
                'user'  => $this->user,
                'updatedFields' => $this->updatedFields
            ])->subject($this->subject);
    }
}

