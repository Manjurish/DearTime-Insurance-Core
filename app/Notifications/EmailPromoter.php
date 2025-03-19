<?php     

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class EmailPromoter extends Notification
{
    use Queueable;


    var $promoter_name;
    var $title;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($promoter_name, $uuid, $title = "")
    {
        $this->promoter_name = $promoter_name;
        $this->uuid = $uuid;
        $this->title = $title == "" ? $title : __('web/mobileVerify.promote');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->view('mail.emailPromoter', [
                'title' => $this->title,
                'content' => $this->promoter_name . __('web/mobileVerify.invite_register'),
                'buttons' => [['link' => route('register', ['uuid' => encrypt($this->uuid)]), 'text' => __('web/mobileVerify.register_now')]],
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
