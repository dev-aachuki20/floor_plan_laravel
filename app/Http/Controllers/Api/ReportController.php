<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\RotaSession;
use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use App\Jobs\SessionConfirmationMail;
use App\Models\Speciality;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Mail;


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
            $sessions = RotaSession::whereDate('week_day_date', $date)
                ->whereHas('rotaDetail', function ($query) use ($hospitalId) {
                    $query->where('hospital_id', $hospitalId);
                })
                ->get(); //for ex: date 2024-08-01 total data is 3

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


    public function confirmAvailability(Request $request)
    {
        $validatedData = $request->validate([
            'rota_session_ids' => 'required|array',
            // 'rota_session_ids.*' => 'integer|exists:rota_session_users,rota_session_id',
            'rota_session_ids.*' => 'integer|exists:rota_sessions,id',
            'user_id'    => 'required|integer|exists:users,id',
        ]);

        $user = User::find($validatedData['user_id']);
        $sessionIds = $validatedData['rota_session_ids'];
        // dd($sessionIds);

        // Check if the user is an anesthetic lead
        if ($user->is_anesthetic_lead) {
            // Confirm availability for each session
            foreach ($sessionIds as $sessionId) {
                $session = RotaSession::find($sessionId);
                $hospitalAdmin = $session->rotaDetail->hospitalDetail;
                dd($hospitalAdmin);
                if ($session) {
                    $session->users()->updateExistingPivot($user->id, ['status' => 1]);
                }
            }

            /// Dispatch job to send emails
            // dispatch(new SessionConfirmationMail($session, $user));
            dispatch(new SessionConfirmationMail($sessionIds, $user));
            return response()->json(['success' => true, 'message' => 'Session confirmed and email sent.']);
        }

        return response()->json(['success' => false, 'message' => 'User is not authorized to confirm this session.']);
    }
}
