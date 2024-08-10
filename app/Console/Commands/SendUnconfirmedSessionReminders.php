<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\RotaSession;
use App\Mail\SessionReminderMail;
use Symfony\Component\HttpFoundation\Response;


class SendUnconfirmedSessionReminders extends Command
{
    protected $signature = 'sessions:remind-unconfirmed';
    protected $description = 'Send reminders for unconfirmed sessions after a week';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sevenDaysAgo = now()->subDays(7);

        $dates = collect(range(1, 7))->map(function ($day) {
            return now()->subDays($day)->startOfDay();
        });

        // Initialize a collection to hold unconfirmed sessions
        $unconfirmedSessions = collect();
        foreach ($dates as $date) {
            $sessions = RotaSession::whereHas('users', function ($query) {
                $query->where('status', 0);
            })
                ->whereDate('week_day_date', $date)
                ->with('users')
                ->get();
            $unconfirmedSessions = $unconfirmedSessions->merge($sessions);
        }

        $usersToNotify = $unconfirmedSessions->flatMap(function ($session) {

            // Get the confirmed user role
            $confirmedUserRole = $session->users->filter(function ($user) {
                return $user->pivot->status == 1;
            })->pluck('pivot.role_id')->unique();

            // Get users with unconfirmed status and roles different from the confirmed user role
            return $session->users->filter(function ($user) use ($confirmedUserRole) {
                return $user->pivot->status == 0 && !$confirmedUserRole->contains($user->pivot->role_id);
            });
        })->unique('id');

        // Send email reminders
        foreach ($usersToNotify as $user) {
            Mail::to($user->email)->queue(new SessionReminderMail($unconfirmedSessions));
        }

        // $this->info('Reminders sent successfully.');

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.reminder_send'),
        ])->setStatusCode(Response::HTTP_OK);
    }
}
