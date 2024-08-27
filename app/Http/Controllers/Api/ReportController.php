<?php

namespace App\Http\Controllers\Api;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Hospital;
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
            'hospital_id' => [
                'required',
                'sometimes',
                function ($attribute, $value, $fail) {
                    if ($value != 0) {
                        if (!DB::table('hospital')->where('id', $value)->whereNull('deleted_at')->exists()) {
                            $fail('The selected hospital is invalid.');
                        }
                    }
                },
            ],
        ]);

        try {
                
            $year = $validatedData['year'] ?? Carbon::now()->year;
            $month = $validatedData['month'] ?? null;
            $hospitalId = $validatedData['hospital_id'];

            $usersQuery = User::select(DB::raw('YEAR(last_login_at) as year'), DB::raw('MONTH(last_login_at) as month'), DB::raw('COUNT(*) as count'));

        
            if($hospitalId == '0'){ 
                
               if(!auth()->user()->is_system_admin){
                    $allHospital = auth()->user()->getHospitals()->pluck('id')->toArray();

                    $usersQuery = $usersQuery->whereRelation('getHospitals', function ($query) use ($allHospital) {
                        $query->whereIn('hospital.id', $allHospital);
                    }); 
                    
                    if(auth()->user()->is_trust_admin){
                        $usersQuery = $usersQuery->where('primary_role', '!=', config('constant.roles.trust_admin'));
                    }else if(auth()->user()->is_hospital_admin){
                        $usersQuery = $usersQuery->whereNotIn('primary_role', [config('constant.roles.trust_admin'),config('constant.roles.hospital_admin')]);
                    }

                }
                
            }else{
                $usersQuery = $usersQuery->join('user_hospital', 'user_hospital.user_id', '=', 'users.id')
                ->where('user_hospital.hospital_id', $hospitalId);
            }
                
            $usersQuery = $usersQuery->where('primary_role', '!=', config('constant.roles.system_admin'))
                ->whereNull('users.deleted_at');

            //Start Filter
            if ($month) {
              $usersQuery->where('last_login_at', '>=', Carbon::now()->subMonths($month)->startOfMonth());
            } else {
                $usersQuery->where('last_login_at', '>=', Carbon::now()->subMonths(11)->startOfMonth());
            }

            if ($year) {
                $usersQuery = $usersQuery->whereYear('last_login_at', $year);
            }
            //End Filter
           
            $usersQuery = $usersQuery->groupBy(DB::raw('YEAR(last_login_at)'), DB::raw('MONTH(last_login_at)'))
            ->orderByRaw('YEAR(last_login_at) DESC, MONTH(last_login_at) DESC');

            $users = $usersQuery->get();

            $startMonth = $month ?: Carbon::now()->month;
            $startYear = $year ?: Carbon::now()->year;

            // Generate month and year in ascending order starting from the current month and year
            $monthYearNames = collect(range(0, 11))->map(function ($i) use ($startMonth, $startYear) {
                $date = Carbon::create($startYear, $startMonth, 1)->addMonths($i);
                return [
                    'month_year' => $date->format('M Y'),  // Format as "DEC 2024"
                    'month' => $date->month,
                    'year' => $date->year,
                ];
            });

            // Prepare data
            $data = $monthYearNames->map(function ($monthYear) use ($users) {
                $currentYear = $monthYear['year'];
                $currentMonth = $monthYear['month'];

                $userRecord = $users->firstWhere('month', $currentMonth);

                if ($userRecord && $userRecord->year == $currentYear) {
                    return [
                        'month_year' => $monthYear['month_year'],
                        'count' => $userRecord->count,
                    ];
                } else {
                    return [
                        'month_year' => $monthYear['month_year'],
                        'count' => 0,
                    ];
                }
            });

            $monthYears = $data->pluck('month_year');
            $dataCounts = $data->pluck('count');

            return $this->respondOk([
                'status'    => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'months'    => $monthYears, 
                'data'      => $dataCounts,
            ])->setStatusCode(Response::HTTP_OK);
            
        } catch (\Exception $e) {
            // dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }


    }

}
