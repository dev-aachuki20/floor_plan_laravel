<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\UserNotificationMail;
use App\Mail\AvailablityStatusMail;
use App\Mail\RotaSessionMail;
use App\Models\User;


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
        // return ['database'];

    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {

        $subject = $this->data['subject'];
        $userName = $notifiable->full_name;
        $message = $this->data['message'];

        if($this->data['notification_type'] == 'session_available'){

            if(isset($this->data['rota_session'])){

                $rotaSession = $this->data['rota_session'];
                return (new RotaSessionMail($subject, $notifiable, $rotaSession))->to($notifiable->user_email);

            }

        }

        if(in_array($this->data['notification_type'], array('session_confirmed','session_cancelled'))){

            if(isset($this->data['rota_session'])){

                $rotaSession = $this->data['rota_session'];

                $authUser = null;
                if($this->data['created_by']){
                    $authUser = User::find($this->data['created_by']);
                }

                $notificationType = $this->data['notification_type'];

                return (new AvailablityStatusMail($subject, $notifiable, $rotaSession,$authUser,$notificationType))->to($notifiable->user_email);


            }

        }

        // if(in_array($this->data['notification_type'], array('quarter_available'))){

        // }

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
