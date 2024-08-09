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
    // Method to get weekly performnce percentage 
    public function getSpecialtyPerformance(Request $request)
    {
        $validatedData = $request->validate([
            'week_days' => ['required', 'array'],
            'hospital'  => ['required', 'integer', 'exists:hospital,id,deleted_at,NULL'],
        ]);

        $weekDays = $validatedData['week_days'];
        $hospitalId = $validatedData['hospital'];

        $performanceData = [];

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

        return response()->json([
            'success' => true,
            'data' => $performanceData,
            'weekly_percentage_overview' => round($weeklyPercentageOverview, 2),
            'weekly_total_session' => $totalSessions,
            'weekly_total_confirm_session' => $totalConfirmed,
            'weekly_total_cancel_session' => $totalCancelled,
        ]);
    }


    public function confirmAvailability(Request $request)
    {
        $validatedData = $request->validate([
            'rotasession_id' => 'required|integer|exists:rota_session_users,rota_session_id',
            'user_id'    => 'required|integer|exists:users,id',
        ]);

        $user = User::find($validatedData['user_id']);
        $session = RotaSession::find($validatedData['rotasession_id']);
        // dd($session);
        // Check if the user is an anesthetic lead
        if ($user->is_anesthetic_lead) {
            // Confirm availability
            $session->users()->updateExistingPivot($user->id, ['status' => 1]);
            /// Dispatch job to send emails
            dispatch(new \App\Jobs\SessionConfirmationMail($session, $user));
            
            return response()->json(['success' => true, 'message' => 'Session confirmed and emails sent.']);
        }

        return response()->json(['success' => false, 'message' => 'User is not authorized to confirm this session.']);
    }
}
