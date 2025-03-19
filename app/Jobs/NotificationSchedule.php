<?php     

namespace App\Jobs;

use App\Http\Controllers\Api\UserController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificationSchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notifications = \App\Notification::whereJsonContains('data->auto_reminder',true)->where("is_read","0")->get();
        foreach ($notifications as $notification) {
            $data = json_decode($notification);
            if(empty($data->remind_after))
                continue;

            if($notification->created_at->diffInDays(now()) >= $data->remind_after) {
                $new_notification = $notification->replicate();
                $data = json_decode($new_notification->data,true);
                $data['auto_reminder'] = false;
                $data['remind_after'] = 0;
                $new_notification->data = json_encode($data);
                $new_notification->created_at = now();
                $new_notification->updated_at = now();
                $new_notification->save();
                $notification->delete();
            }

        }
        $notifications = \App\Notification::whereJsonContains('data->auto_answer',true)->where("is_read","0")->get();
        foreach ($notifications as $notification) {
            $data = json_decode($notification);
            if (empty($data->auto_answer_details))
                continue;
            $days = $data->auto_answer_details->days ?? 0;
            $action = $data->auto_answer_details->action ?? null;
            if(empty($days) || empty($action))
                continue;
            if($notification->created_at->diffInDays(now()) >= $data->remind_after) {
                $request = request();

                $request->request->replace([
                   'uuid'=>$notification->uuid,
                   'action'=>$action,
                ]);

                $out = app(UserController::class)->msgAction($request);
            }

        }
    }
}
