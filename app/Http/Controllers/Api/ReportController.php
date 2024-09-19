<?php

namespace App\Http\Controllers\Api;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Hospital;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;


class ReportController extends APIController
{
    /**
     * Display a listing of reports filtered by week days and hospital.
     *
     * Validates the request data before proceeding and returns a JSON response.
     * 
     * @param  \Illuminate\Http\Request  $request
     *         The incoming request containing 'week_days' and 'hospital' data.
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     *         JSON-encoded response containing success message and validated data 
     *         or error message if the process fails.
     * 
     * @throws \Illuminate\Validation\ValidationException
     *         Throws an exception if the request validation fails.
     *
     * Validations:
     * - 'week_days': Must be provided as an array of days.
     * - 'hospital': Must be an integer and reference an existing, non-deleted hospital.
     */
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
            \Log::info('Error in ReportController::index (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
     * Generate a report chart filtered by month, year, and hospital.
     *
     * This function validates the incoming request data and applies custom validation 
     * logic for the hospital_id. If validation passes, the chart data can be processed.
     * 
     * @param  \Illuminate\Http\Request  $request
     *         The incoming request containing optional 'month', 'year', and required 'hospital_id'.
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     *         A JSON response with the report chart data (to be implemented).
     * 
     * @throws \Illuminate\Validation\ValidationException
     *         Throws an exception if the request validation fails.
     *
     * Validations:
     * - 'month': Optional string, must be a valid month (01-12) using a regular expression.
     * - 'year': Optional string, must be exactly 4 characters long.
     * - 'hospital_id': Required and must reference an existing, non-deleted hospital if not 0.
     * 
     * Custom Logic:
     * - 'hospital_id': If not 0, a custom validation checks the existence of the hospital
     *   in the database, ensuring it hasn't been deleted (checked via the 'deleted_at' field).
     */
    public function reportChart(Request $request)
    {
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

            $userActivityQuery = UserActivity::select(
                DB::raw('YEAR(login_date) as year'),
                DB::raw('MONTH(login_date) as month'),
                DB::raw('COUNT(DISTINCT user_id) as count')
            );

            $user = auth()->user();
            if (!auth()->user()->is_system_admin) {
                
               $userActivityQuery = $userActivityQuery->whereHas('user', function ($query) use ($user) {
                    
                    $query->withTrashed();

                    $query->when($user->is_trust_admin, function ($q) {
                        $q->whereNotIn('primary_role', [config('constant.roles.trust_admin')]);
                    })
                    ->when($user->is_hospital_admin, function ($q) {
                        $q->whereNotIn('primary_role', [
                            config('constant.roles.trust_admin'), 
                            config('constant.roles.hospital_admin'),
                            config('constant.roles.chair')
                        ]);
                    })
                    ->when($user->is_chair, function ($q) {
                        $q->whereNotIn('primary_role', [
                            config('constant.roles.trust_admin'), 
                            config('constant.roles.chair')
                        ]);
                    });
                });

                $allHospitalIds = auth()->user()->getHospitals()->pluck('id')->toArray();
                $userActivityQuery = $userActivityQuery->whereIn('hospital_id', $allHospitalIds);
            }

            if ($hospitalId != '0') {
                $userActivityQuery = $userActivityQuery->where('hospital_id', $hospitalId);
            }

           
            if ($month) {
                $userActivityQuery->where('login_date', '>=', Carbon::now()->subMonths($month)->startOfMonth());
            } else {
                $userActivityQuery->where('login_date', '>=', Carbon::now()->subMonths(11)->startOfMonth());
            }

            if ($year) {
                $userActivityQuery = $userActivityQuery->whereYear('login_date', $year);
            }

            
            $userActivityQuery = $userActivityQuery->groupBy(DB::raw('YEAR(login_date)'), DB::raw('MONTH(login_date)'))
                ->orderByRaw('YEAR(login_date) DESC, MONTH(login_date) DESC');

            // Get results
            $userActivities = $userActivityQuery->get();

            $startMonth = $month ?: Carbon::now()->month;
            $startYear = $year ?: Carbon::now()->year;

            // Generate month and year in ascending order starting from the current month and year
            $monthYearNames = collect(range(0, 11))->map(function ($i) use ($startMonth, $startYear) {
                $date = Carbon::create($startYear, $startMonth, 1)->addMonths($i);
                return [
                    'month_year' => $date->format('M Y'),
                    'month' => $date->month,
                    'year' => $date->year,
                ];
            });

            // Prepare data
            $data = $monthYearNames->map(function ($monthYear) use ($userActivities) {
                $currentYear = $monthYear['year'];
                $currentMonth = $monthYear['month'];

                $userActivityRecord = $userActivities->firstWhere('month', $currentMonth);

                if ($userActivityRecord && $userActivityRecord->year == $currentYear) {
                    return [
                        'month_year' => $monthYear['month_year'],
                        'count' => $userActivityRecord->count,
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
                'status' => true,
                'message' => trans('messages.record_retrieved_successfully'),
                'months' => $monthYears,
                'data' => $dataCounts,
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            dd('Error in ReportController::reportChart (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());

            \Log::info('Error in ReportController::reportChart (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

}
