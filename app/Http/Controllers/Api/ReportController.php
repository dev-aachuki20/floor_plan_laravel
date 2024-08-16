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
    }

}
