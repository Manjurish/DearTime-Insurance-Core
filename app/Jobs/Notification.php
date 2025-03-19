<?php     

namespace App\Jobs;

use App\Helpers;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Notification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    var $message;
    var $data;
    var $user_id;
    var $title;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$title,$message,$data)
    {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::WithPendingPromoted()->find($this->user_id);
        if(empty($user))
            return;

        $message = new \App\Notification();
        $message->title = $this->title;
//        $message->text = mb_substr($this->message,0,150).' ...';
        $message->text = $this->message;
        $message->data = json_encode($this->data ?? []);
        $message->is_read = '0';
        $message->user_id = $user->id;
        $message->full_text = $this->message;
        $message->auto_read = $this->data['auto_read'] ?? 1;

        $message->save();

        $this->message = $message->full_text;
        $this->title = $message->title;

        foreach ($user->notificationTokens as $notificationToken) {
            if($notificationToken->os == 'android')
                Helpers::sendNotificationToAndroid($this->title,$this->message,$this->data,$notificationToken->token);
            elseif($notificationToken->os == 'ios')
                Helpers::sendNotificationToIOS($this->title,$this->message,$this->data,$notificationToken->token);
        }
    }
}
