<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserNotificationMail extends Mailable implements ShouldQueue 
{
    use Queueable, SerializesModels;

    public $subject, $userName, $message;
  
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $userName, $message)
    {
        $this->subject  = $subject;
        $this->userName = $userName;
        $this->message  = $message;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        \Log::info('Mail Send');
        return $this->markdown('emails.user-notification-mail', [
                'userName'  => $this->userName,
                'message' => $this->message
            ])->subject($this->subject);
    }
}

