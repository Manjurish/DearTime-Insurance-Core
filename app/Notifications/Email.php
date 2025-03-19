<?php     

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class Email extends Notification
{
    use Queueable;


    var $content;
    var $buttons;
    var $title;
    var $downloadApp;
    var $referrer;
    var $confetti;
    var $attachments;
    var $subject;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($content,$data=[])
    {
        $this->content = $content;
        $this->subject = $data['subject'] ?? '';
        $this->buttons = $data['buttons'] ?? [];
        $this->title = $data['title'] ?? '';
        $this->downloadApp = $data['downloadApp'] ?? false;
        $this->referrer = $data['referrer'] ?? false;
        $this->confetti = $data['confetti'] ?? false;
        $this->attachments = $data['attachments'] ?? [];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
                    ->view('mail.email',
                        [
                            'content'=>$this->content,
                            'buttons'=>$this->buttons,
                            'title'=>$this->title,
                            'downloadApp'=>$this->downloadApp,
                            'referrer'=>$this->referrer,
                            'confetti'=>$this->confetti
                        ]
                    )
                    ->subject($this->subject);
        foreach ($this->attachments as $attachment){
            $mail->attachData($attachment['file'],$attachment['name']);
        }
        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
