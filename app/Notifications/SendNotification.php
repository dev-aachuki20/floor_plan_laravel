<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\UserNotificationMail;
use App\Mail\SetQuarterSessionMail;
use App\Mail\RotaSessionMail;
use App\Mail\RotaSessionClosedMail;
use App\Models\User;
use App\Models\RotaSession;


use Auth;

class SendNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {

        return ['mail', 'database'];

    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {

        $subject = $this->data['subject'];
        $userName = $notifiable->full_name;
        $message = $this->data['message'];

        if(in_array($this->data['notification_type'], array('session_available','first_reminder','final_reminder'))){

            if(isset($this->data['rota_session'])){

                $rotaSession = $this->data['rota_session'];
                return (new RotaSessionMail($subject, $notifiable, $rotaSession))->to($notifiable->user_email);

            }

        }

        if(in_array($this->data['notification_type'], array('session_closed','session_failed'))){

            if(isset($this->data['rota_session'])){

                $rotaSession = $this->data['rota_session'];
                $remainingRolesToConfirm = isset($this->data['remaining_roles_to_confirm']) ? $this->data['remaining_roles_to_confirm'] : null;
                
                return (new RotaSessionClosedMail($subject, $notifiable, $rotaSession,$remainingRolesToConfirm))->to($notifiable->user_email);

            }

        }


        if(in_array($this->data['notification_type'], array('quarter_available'))){

            if(isset($this->data['rota_session_ids'])){

               $allRotaSessions = RotaSession::whereIn('id',$this->data['rota_session_ids'])->get();
                $hospitalName = isset($this->data['hospital_name']) ? $this->data['hospital_name'] : null;
               return (new SetQuarterSessionMail($subject, $notifiable, $hospitalName ,$allRotaSessions))->to($notifiable->user_email);

            }
        }

        if(in_array($this->data['notification_type'], array('quarter_saved'))){
            $quarterNo = isset($this->data['quarterNo']) ? $this->data['quarterNo'] : null;
            $quarterYear = isset($this->data['quarterYear']) ? $this->data['quarterYear'] : null;

            $message = trans('messages.mail_content.quarter_saved',['quarterNo'=>$quarterNo,'quarterYear'=>$quarterYear]);
        }

        if(in_array($this->data['notification_type'], array('user_deleted_by_own'))){
            $authUser = isset($this->data['authUser']) ? $this->data['authUser'] : null;
            $message = trans('messages.mail_content.user_deleted_by_own',['user_name'=> $authUser->full_name,'user_email'=> $authUser->user_email]);
        }

        if(in_array($this->data['notification_type'], array('user_deleted_by_admin'))){
            $deletedUser = isset($this->data['deletedUser']) ? $this->data['deletedUser'] : null;
            $authUser = isset($this->data['authUser']) ? $this->data['authUser'] : null;

            $message = trans('messages.mail_content.user_deleted_by_admin',['user_name'=>$deletedUser->full_name,'user_email'=>$deletedUser->user_email,'admin_name'=>$authUser->full_name]);
        }

        return (new UserNotificationMail($subject, $userName, $message))->to($notifiable->user_email);

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subject' => $this->data['subject'],
            'message' => $this->data['message'],
            'section' => $this->data['section'],
        ];
    }
}
