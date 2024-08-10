<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\RotaSession;
use App\Models\User;

class SessionConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sessionIds;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param RotaSession $session
     * @param User $user
     */
    public function __construct(array $sessionIds, $user)
    {
        $this->sessionIds = $sessionIds;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.session_confirmed')
            ->subject('Session Confirmed')
            ->with([
                'session' => $this->sessionIds,
                'user' => $this->user,
            ]);
    }
}
