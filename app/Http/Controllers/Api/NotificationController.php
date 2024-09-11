<?php

namespace App\Http\Controllers\Api;


use DB;
use App\Models\User;
use App\Models\RotaSession;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;


class NotificationController extends APIController
{
    public function index(Request $request){

        $authUser = auth()->user();

        $notifications = $authUser->notification()->select('id','subject','message','data','notification_type','rota_session_id','read_at','created_at');

        //Start Apply filters
        if ($request->filter_by) {

            if ($request->filter_by == 'type' && $request->filter_value) {

                $filterValue = $request->filter_value;

                if($filterValue == 'read'){

                    $notifications = $notifications->whereNotNull('read_at');

                }else if($filterValue == 'unread'){

                    $notifications = $notifications->whereNull('read_at');

                }else if($filterValue == 'unapproved'){

                    $notifications = $notifications->where('notification_type','session_not_approved');

                }else{
                    $notifications = $notifications->where('notification_type', $filterValue);
                }
                

            }
        }
        //End Apply filters

        if($request->status){
            $notifications = $notifications->whereNull('read_at');
        }

        $perPage = $request->per_page ?? 10;

        $notifications = $notifications->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')->orderBy('created_at','desc')->paginate($perPage);

        $notifications->getCollection()->transform(function ($notification) {
            $notification->created_time = Carbon::parse($notification->created_at)->format('g:i A');

            if($notification->rotaSession){

                $carbonDate = Carbon::parse($notification->rotaSession->week_day_date);
                $formattedDate = $carbonDate->format('D, j M');
                $notification->slot = $formattedDate.' - '.$notification->rotaSession->time_slot;
                $notification->hospital_id = $notification->rotaSession->hospital_id;
                $notification->session_date = $notification->rotaSession->week_day_date;

            }else{
                $notification->hospital_id = isset($notification->data['hospital_id']) ? $notification->data['hospital_id'] : null;
                $notification->session_date = isset($notification->data['session_date']) ? $notification->data['session_date'] : null;
            }

            $notification->makeHidden(['data']);

            return $notification;
        });


        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $notifications,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function makeAsRead($uuid){

        DB::beginTransaction();
        try {
            $authUser = auth()->user();

            $notification = $authUser->notification()->where('id',$uuid)->update(['read_at'=>now()]);

            if (!$notification) {
                return $this->respondOk([
                    'status'   => true,
                    'message'   => trans('messages.notification.not_found')
                ])->setStatusCode(Response::HTTP_OK);
            }

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.notification.mark_as_read')
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->throwValidation([trans('messages.error_message')]);
        }
    }


}
