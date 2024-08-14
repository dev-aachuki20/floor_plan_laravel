<?php

namespace App\Http\Controllers\Api;


use DB;
use App\Models\User;
use App\Models\RotaSession;
use App\Notifications\SendNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;


class NotificationController extends APIController
{
    public function index(Request $request){

        $authUser = auth()->user();

        $notifications = $authUser->notification();

        //Start Apply filters
        if ($request->filter_by) {

            if ($request->filter_by == 'type' && $request->filter_value) {

                $notifications = $notifications->where('notification_type', $request->filter_value);

            }
        }
        //End Apply filters

        $notifications = $notifications->orderBy('created_at','desc')->paginate(10);

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
            // return $this->throwValidation([$e->getMessage()]);
            return $this->throwValidation([trans('messages.error_message')]);
        }
    }


    public function sendmailToUser($user){

        $user = User::find($user);

        $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));
        $messageData = [
            'notification_type' => array_search(config('constant.subject_notification_type.session_confirmed'), config('constant.subject_notification_type')),
            'section'           => $key,
            'subject'           => 'Session is available',
            'message'           => 'Hello session is available',
            'rota_session'      => null,
        ];

        $user->notify(new SendNotification($messageData));

        return $this->respondOk([
            'status'   => true,
            'message'   => 'Send Notification',
        ])->setStatusCode(Response::HTTP_OK);
    }



}
