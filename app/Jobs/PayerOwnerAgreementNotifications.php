<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Coverage;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use Illuminate\Support\Facades\App;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Carbon;

class PayerOwnerAgreementNotifications implements ShouldQueue
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
        try {
            //DB::enableQueryLog();
            //FIRST REMAINDER ON 4TH DAY
            $firstCoverages   =   Coverage::whereIn('status', [Enum::COVERAGE_STATUS_UNPAID, Enum::COVERAGE_STATUS_INCREASE_UNPAID])
                ->where(['state' => Enum::COVERAGE_STATE_INACTIVE])
                ->whereRaw("ADDDATE(DATE(created_at), INTERVAL 3 DAY) = CURDATE()")
                ->groupBy('owner_id')
                ->orderBy('id')
                ->get();
            //FIRST REMAINDER ON 8TH DAY
            $secondCoverages   =   Coverage::whereIn('status', [Enum::COVERAGE_STATUS_UNPAID, Enum::COVERAGE_STATUS_INCREASE_UNPAID])
                ->where(['state' => Enum::COVERAGE_STATE_INACTIVE])
                ->whereRaw("ADDDATE(DATE(created_at), INTERVAL 7 DAY) = CURDATE()")
                ->groupBy('owner_id')
                ->orderBy('id')
                ->get();
            //FIRST REMAINDER ON 15TH DAY
            $thirdCoverages   =   Coverage::whereIn('status', [Enum::COVERAGE_STATUS_UNPAID, Enum::COVERAGE_STATUS_INCREASE_UNPAID])
                ->where(['state' => Enum::COVERAGE_STATE_INACTIVE])
                ->whereRaw("ADDDATE(DATE(created_at), INTERVAL 14 DAY) = CURDATE()")
                ->groupBy('owner_id')
                ->orderBy('id')
                ->get();
            //dd(DB::getQueryLog());
            //dd($firstCoverages, $secondCoverages, $thirdCoverages);

            foreach ($firstCoverages ?? [] as $coverage) {
                $this->sendNotification($coverage, 1);
            }
            foreach ($secondCoverages ?? [] as $coverage) {
                $this->sendNotification($coverage, 2);
            }
            foreach ($thirdCoverages ?? [] as $coverage) {
                $this->sendNotification($coverage, 3);
            }
            return ['status' => 'success', 'data' => ['info' => 'Email sent successfully!']];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function sendNotification($coverage, $remainder = 1)
    {
        $payer  =   $coverage->payer ?? '';
        $owner  =   $coverage->owner ?? '';
        $locale = $owner->user->locale;
        App::setLocale($locale ?? 'en');

        if (!$payer) return false;
        if (!$owner) return false;

        if ($payer->profile->id == $owner->id)   return false;

        if($payer->corporate_type=='payorcorporate'){
          
                return false;
            
        }

        if (empty($owner->user->password)) {       //NON DT USER/NEW USER
            $emailText = __('mobile.payor_owner_agreement_non_dtuser', [
                'payer_name' => $payer->name,
                'owner_name' => $owner->name,
            ]);
        } else {
            $emailText = __('mobile.payor_owner_agreement', [
                'payer_name' => $payer->name,
                'owner_name' => $owner->name,
            ]);
        }
        $owner->user->sendNotification(
            __('notification.payor_owner_agreement.title'),
            strip_tags($emailText),
            [
                'command' => 'next_page',
                'translate_data' => [
                    'payer_name' => $payer->name,
                    'coverages' => ''
                ],
                'page_data' => [
                    'fill_type' => 'pay_for_others',
                    'payer_id' => $payer->uuid,
                    'user_id' => $coverage->covered->uuid ?? 0
                ],
                'data' => NextPage::POLICIES,
                'id' => 'pay_other',
                'buttons' => [
                    ['title' => 'accept', 'action' => 'accept_pay_other'],
                    ['title' => 'reject', 'action' => 'reject_pay_other_confirm']
                ],
                'auto_read' => FALSE,
                'auto_reminder' => TRUE,
                'remind_after' => 3,
                'auto_answer' => TRUE,
                'auto_answer_details' => ['days' => 5, 'action' => 'reject_pay_other_confirm']
            ]
        );

        $owner->user->notify(new \App\Notifications\Email($emailText, ['subject' => __('notification.payor_owner_agreement.title') . " - Reminder {$remainder}"]));
    }
}
