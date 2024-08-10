<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RotaSession;
use App\Models\User;
use App\Mail\SessionConfirmedMail;
use Illuminate\Support\Facades\Mail;

class SessionConfirmationMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $sessionIds;
    protected $user;
    protected $hospitalAdmin;
    protected $trustAdmins;
    protected $systemAdmin;

    /**
     * Create a new job instance.
     *
     * @param array $sessionIds
     * @param User $user
     */
    public function __construct(array $sessionIds, User $user, $hospitalAdmin, $trustAdmins, $systemAdmin)
    {
        $this->sessionIds = $sessionIds;
        $this->user = $user;
        $this->hospitalAdmin = $hospitalAdmin;
        $this->trustAdmins   = $trustAdmins;
        $this->systemAdmin   = $systemAdmin;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $recipients = collect([$this->hospitalAdmin])->merge($this->trustAdmins)->merge($this->systemAdmin);

        foreach ($recipients as $recipient) {
            if ($recipient->id !== $this->user->id) {
                Mail::to($recipient->email)->queue(new SessionConfirmedMail($this->sessionIds, $this->user));
            }
        }
    }
}
