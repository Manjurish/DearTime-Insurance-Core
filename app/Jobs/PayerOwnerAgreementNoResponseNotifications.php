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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class PayerOwnerAgreementNoResponseNotifications implements ShouldQueue
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
            //NO RESPONSE NOTIFICATIONS ON 22nd DAY
            $thirdCoverages   =   Coverage::whereIn('status', [Enum::COVERAGE_STATUS_UNPAID, Enum::COVERAGE_STATUS_INCREASE_UNPAID])
                ->where(['state' => Enum::COVERAGE_STATE_INACTIVE])
                ->whereRaw("ADDDATE(DATE(created_at), INTERVAL 21 DAY) = CURDATE()")
                ->groupBy('owner_id')
                ->orderBy('id')
                ->get();
            //dd(DB::getQueryLog());
            //dd($thirdCoverages);

            foreach ($thirdCoverages ?? [] as $coverage) {
                $this->sendNotification($coverage);
            }
            return ['status' => 'success', 'data' => ['info' => 'Email sent successfully!']];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function sendNotification($coverage)
    {

       
        $payer  =   $coverage->payer ?? '';
        $owner  =   $coverage->owner ?? '';
        $locale = $payer->locale;
        App::setLocale($locale ?? 'en');

        if (!$payer) return false;
        if (!$owner) return false;

        if ($payer->profile->id == $owner->id)   return false;

        if($payer->corporate_type == 'payorcorporate'){
            if($coverage->corporate_user_status == 'accepted'){
                return false;
            }
        }

        if($coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID)
            $coverage->status   =   Enum::COVERAGE_STATUS_INCREASE_TERMINATE;
        else
            $coverage->status   =   Enum::COVERAGE_STATUS_TERMINATE;

        $coverage->state    =   Enum::COVERAGE_STATE_INACTIVE;
        $coverage->save();

        $emailText = __('mobile.payor_owner_agreement_no_response', [
            'payer_name' => $payer->name,
            'owner_name' => $owner->name,
            'him_her'     => ($owner->gender == 'female') ? 'her' : 'him' ?? 'him',
        ]);

        $payer->sendNotification(
            __('notification.payor_owner_agreement.title'),
            strip_tags($emailText),
            [
                'command' => 'next_page',
                'translate_data' => [
                    'payer_name' => $payer->name,
                    'coverages' => ''
                ],
                'page_data' => [
                    'fill_type' => 'buy_for_others_apply_again',
                    'payer_id' => $payer->uuid,
                    'user_id' => $coverage->covered->uuid ?? 0
                ],
                'data' => NextPage::POLICIES,
                'id' => 'pay_other',
                'auto_read' => FALSE,
                'auto_reminder' => TRUE,
                'remind_after' => 3,
                'auto_answer' => TRUE,
            ]
        );

        $payer->notify(new \App\Notifications\Email($emailText, ['subject' => __('notification.payor_owner_agreement.title') . " - Cancelled"]));
    }
}
