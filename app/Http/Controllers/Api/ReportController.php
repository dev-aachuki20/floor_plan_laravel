<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\RotaSession;
use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use App\Jobs\SessionConfirmationMail;
use App\Mail\SessionReminderMail;
use App\Models\Speciality;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Mail;
use Illuminate\Support\Facades\Log;


class ReportController extends APIController
{
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'week_days' => ['required', 'array'],
            'hospital'  => ['required', 'integer', 'exists:hospital,id,deleted_at,NULL'],
        ]);

        $weekDays = $validatedData['week_days'];
        $hospitalId = $validatedData['hospital'];

        $performanceData = [];

        $roleBasedTotals = [
            'speciality' => ['total' => 0, 'confirmed' => 0, 'cancelled' => 0],
            'anaesthetics' => ['total' => 0, 'confirmed' => 0, 'cancelled' => 0],
            'staff' => ['total' => 0, 'confirmed' => 0, 'cancelled' => 0],
        ];

        foreach ($weekDays as $date) {
            // Query for the sessions on each date
            $sessions = RotaSession::whereDate('week_day_date', $date)->where('hospital_id', $hospitalId)->get(); 

            foreach ($sessions as $session) {
                $totalSessions = $session->users->count();
                $confirmedSessions = $session->users->where('pivot.status', 1)->count();
                $cancelledSessions = $session->users->where('pivot.status', 2)->count();

                $performancePercentage = $totalSessions ? ($confirmedSessions / $totalSessions) * 100 : 0;

                $performanceData[$date][$session->speciality_id] = [
                    'total_sessions' => $totalSessions,
                    'confirmed_sessions' => $confirmedSessions,
                    'cancelled_sessions' => $cancelledSessions,
                    'performance_percentage' => round($performancePercentage, 2),
                ];

                // Calculate role-based totals
                foreach ($session->users as $user) {
                    switch ($user->primary_role) {
                        case config('constant.roles.speciality_lead'):
                            $role = 'speciality';
                            break;
                        case config('constant.roles.anesthetic_lead'):
                            $role = 'anaesthetics';
                            break;
                        case config('constant.roles.staff_coordinator'):
                            $role = 'staff';
                            break;
                        default:
                            continue 2;
                    }

                    $roleBasedTotals[$role]['total']++;
                    if ($user->pivot->status == 1) {
                        $roleBasedTotals[$role]['confirmed']++;
                    } elseif ($user->pivot->status == 2) {
                        $roleBasedTotals[$role]['cancelled']++;
                    }
                }
            }
        }

        // Calculate the weekly performance overview
        $totalConfirmed = 0;
        $totalSessions  = 0;
        $totalCancelled = 0;

        foreach ($performanceData as $dateData) {
            foreach ($dateData as $data) {
                $totalSessions += $data['total_sessions'];
                $totalConfirmed += $data['confirmed_sessions'];
                $totalCancelled += $data['cancelled_sessions'];
            }
        }

        $weeklyPercentageOverview = $totalSessions ? ($totalConfirmed / $totalSessions) * 100 : 0;

        // Calculate role-based performance percentages
        foreach ($roleBasedTotals as $role => $totals) {
            $roleBasedTotals[$role]['performance_percentage'] = $totals['total'] ? ($totals['confirmed'] / $totals['total']) * 100 : 0;
        }

        $reportOverview['title'] = trans('messages.reports.overview.title');
        $reportOverview['description'] = trans('messages.reports.overview.descriptiion');
        $reportOverview['percentage'] = round($weeklyPercentageOverview, 2);

        $reportsResponse['speciality']['role_id'] = config('constant.roles.speciality_lead');
        $reportsResponse['speciality']['title'] = trans('messages.reports.speciality.title');
        $reportsResponse['speciality']['description'] = trans('messages.reports.speciality.description');
        $reportsResponse['speciality']['percentage'] = round($roleBasedTotals['speciality']['performance_percentage'], 2);


        $reportsResponse['anaesthetics']['role_id'] = config('constant.roles.anesthetic_lead');
        $reportsResponse['anaesthetics']['title'] = trans('messages.reports.anaesthetics.title');
        $reportsResponse['anaesthetics']['description'] = trans('messages.reports.anaesthetics.description');
        $reportsResponse['anaesthetics']['percentage'] = round($roleBasedTotals['anaesthetics']['performance_percentage'], 2);


        $reportsResponse['staff']['role_id'] = config('constant.roles.staff_coordinator');
        $reportsResponse['staff']['title'] = trans('messages.reports.staff.title');
        $reportsResponse['staff']['description'] = trans('messages.reports.staff.description');
        $reportsResponse['staff']['percentage'] = round($roleBasedTotals['staff']['performance_percentage'], 2);



        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data' => [
                // $performanceData,
                /* 'percentage_overview' => round($weeklyPercentageOverview, 2),
                'total_session' => $totalSessions,
                'total_confirm_session' => $totalConfirmed,
                'total_cancel_session' => $totalCancelled,*/
                $reportOverview,
                $reportsResponse

            ],
        ])->setStatusCode(Response::HTTP_OK);
    }



    public function sendSessionReminderForUnconfirmedUsers()
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

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.reminder_send'),
        ])->setStatusCode(Response::HTTP_OK);
    }
}
