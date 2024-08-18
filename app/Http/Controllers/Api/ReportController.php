<?php

namespace App\Http\Controllers\Api;

use DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;


class ReportController extends APIController
{
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'week_days' => ['required', 'array'],
            'hospital'  => ['required', 'integer', 'exists:hospital,id,deleted_at,NULL'],
        ]);

        try {
            
            $weekDays = $validatedData['week_days'];
            $hospitalId = $validatedData['hospital'];

            $reportOverview['title']       = trans('messages.reports.overview.title');
            $reportOverview['description'] = trans('messages.reports.overview.descriptiion');
            $reportOverview['percentage']  = rotaTableReportStatistics($hospitalId,$weekDays);

            $reportsResponse['speciality']['role_id']       = config('constant.roles.speciality_lead');
            $reportsResponse['speciality']['title']         = trans('messages.reports.speciality.title');
            $reportsResponse['speciality']['description']   = trans('messages.reports.speciality.description');
            $reportsResponse['speciality']['percentage']    = rotaTableReportStatistics($hospitalId,$weekDays,config('constant.roles.speciality_lead'));

            $reportsResponse['anaesthetics']['role_id']     = config('constant.roles.anesthetic_lead');
            $reportsResponse['anaesthetics']['title']       = trans('messages.reports.anaesthetics.title');
            $reportsResponse['anaesthetics']['description'] = trans('messages.reports.anaesthetics.description');
            $reportsResponse['anaesthetics']['percentage']  = rotaTableReportStatistics($hospitalId,$weekDays,config('constant.roles.anesthetic_lead'));

            $reportsResponse['staff']['role_id']        = config('constant.roles.staff_coordinator');
            $reportsResponse['staff']['title']          = trans('messages.reports.staff.title');
            $reportsResponse['staff']['description']    = trans('messages.reports.staff.description');
            $reportsResponse['staff']['percentage']     = rotaTableReportStatistics($hospitalId,$weekDays,config('constant.roles.staff_coordinator'));


            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'data' => [
                    $reportOverview,
                    $reportsResponse

                ],
            ])->setStatusCode(Response::HTTP_OK);

        }catch (\Exception $e) {
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }


    public function reportChart(Request $request){
       
        $validatedData = $request->validate([
            'month' => ['nullable', 'string', 'regex:/^(0?[1-9]|1[0-2])$/'],
            'year'  => ['nullable', 'string', 'size:4'], 
            'hospital_id'  => ['required', 'integer', 'exists:hospital,id,deleted_at,NULL'],
        ]);

        try {
                
            $year = $validatedData['year'] ?? Carbon::now()->year;
            $month = $validatedData['month'] ?? null;
            $hospitalId = $validatedData['hospital_id'];

            $users = User::select(DB::raw('MONTH(last_login_at) as month'), DB::raw('COUNT(*) as count'))
                ->join('user_hospital', 'user_hospital.user_id', '=', 'users.id')
                ->where('user_hospital.hospital_id', $hospitalId)
                ->whereYear('last_login_at', $year)
                ->where('primary_role','!=',config('constant.roles.system_admin'));

            if ($month) {
                $users->whereMonth('last_login_at', $month);
            } else {
                $currentMonth = Carbon::now()->month;
                $users->groupBy(DB::raw('MONTH(last_login_at)'))
                    ->orderByRaw('MONTH(last_login_at) = ? DESC', [$currentMonth])
                    ->orderBy('month');
            }

            $users = $users->get();

            // Generate month names dynamically using Carbon
            $fullMonthNames = collect(range(1, 12))->mapWithKeys(function ($i) {
                return [$i => Carbon::create()->month($i)->format('F')];
            });

          
            // Reorder month names to start from the current month
            $monthNames = collect(range(0, 11))->map(function ($i) use ($month, $fullMonthNames) {
                return $fullMonthNames[($month + $i) % 12 + 1];
            });

            $data = $monthNames->mapWithKeys(function ($monthName, $index) use ($users, $month) {
                $currentMonth = ($month + $index - 1) % 12 + 1;
                return [
                    $index => [
                        'month' => $monthName,
                        'count' => $users->firstWhere('month', $currentMonth)->count ?? 0,
                    ]
                ];
            });

            $months = $monthNames->values();
            $data = $data->values()->pluck('count');

            return $this->respondOk([
                'status'    => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'months'    => $months,
                'data'      => $data,
            ])->setStatusCode(Response::HTTP_OK);
            
        } catch (\Exception $e) {
            dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }


    }

}
