<?php     

namespace App\Jobs;

use App\Coverage;
use App\Helpers;
use App\Http\Controllers\User\DocumentController;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Vonage\Voice\NCCO\Action\Input;

class GenerateDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    var $user;
    var $text;
    var $data;
    var $coverages;
    var $subject;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($coverages,$user,$text,$subject,$data=[])
    {
        $this->user = $user;
        $this->text = $text;
        $this->data = $data;
        $this->coverages = $coverages;
        $this->subject = $subject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $links=[];
        $user = User::where('id',$this->user)->first();
        foreach ($this->coverages ?? [] as $coverage) {
            $coverage = Coverage::whereUuid($coverage->uuid)->first();
            if (empty($coverage))
                continue;

////            $req = Request::create('/doc', 'GET', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $coverage->uuid ?? '-1', 'type' => 'contract', 'uuid' => encrypt($coverage->covered->user_id), 'encryption' => Carbon::parse($coverage->owner->dob)->format('dMY'), 'need_save' => true]);
////            app()->handle($req);
//
//            $request = new \Illuminate\Http\Request(['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $coverage->uuid ?? '-1', 'type' => 'contract', 'uuid' => encrypt($coverage->covered->user_id), 'encryption' => Carbon::parse($coverage->owner->dob)->format('dMY'), 'need_save' => true]);
////            $request->replace(['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $coverage->uuid ?? '-1', 'type' => 'contract', 'uuid' => encrypt($coverage->covered->user_id), 'encryption' => Carbon::parse($coverage->owner->dob)->format('dMY'), 'need_save' => true]);
//            $docController=new DocumentController();
//            $docController->generateDoc($request);
            
            $locale = $user->locale;
            App::setLocale($locale ?? 'en');
            $pdf_password     =   substr($coverage->owner->nric, -4).Carbon::parse($coverage->owner->dob)->format('Y');
            $req = DocumentController::g_contract($coverage->uuid, $locale , $pdf_password);            

            $payload = [
                'json' => $req,
            ];

            $response = Http::asJson()->retry(3, 5)->post(env('FACE_API_URL'), $payload);
            $file = $response->body();

            $coverage = Coverage::whereUuid($coverage->uuid)->first();
            Helpers::createDocumentFromFile($file, $coverage, 'contract', false, 'pdf');

            $links[] = [
                'file' => Storage::disk('s3')->get($coverage->documents()->latest()->first()->path),
                'name' => $coverage->product_name . '.' . $coverage->documents()->latest()->first()->ext
            ];

        }
        if (!empty($user)) {
            $user->notify(new \App\Notifications\Email($this->text,[
                'subject'  =>$this->subject,
                'confetti' => TRUE,
                'attachments' => $links
            ]));
        }
    }
}
