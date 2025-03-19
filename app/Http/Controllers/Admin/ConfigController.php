<?php

namespace App\Http\Controllers\Admin;

use App\Claim;

use App\Config;
use App\Jobs\Age;
use App\Jobs\CancelPendingCoverage;
use App\Jobs\DecreaseCoverage;
use App\Jobs\FulfilledCoverage;
use App\Jobs\PromoterRefund;
use App\Jobs\RecalculateCoverage;
use App\Jobs\RenewCoverage;
use App\Jobs\RetryPendingOrder;
use Illuminate\Http\Request;
use Mmeshkatian\Ariel\BaseController;
use Spatie\TestTime\TestTime;


class ConfigController extends ArielController
{
    public function configure()
    {
        $this->model = Config::class;
        $this->set('isSingleRow', true);
        $this->setTitle("Setting");

        $this->addField("key[system_extra_day]", "Add Day to System", '', '', Config::getValue('system_extra_day'));
        $this->addField("key[system_extra_hour]", "Add Hour to System", '', '', Config::getValue('system_extra_hour'));
        $this->addField("key[user_screening]", "User Screening", '', 'select', Config::getValue('user_screening'), ['active' => 'Active', 'deactive' => 'Deactive']);
        //EKYC changes
        $this->addField("key[face_comparision]", "Face Comparision", '', 'select', Config::getValue('face_comparision'), ['active' => 'Active', 'deactive' => 'Deactive']);
        $this->addField("key[default_face_compare_result]", "Face Comparision Default Result", '', 'select', Config::getValue('default_face_compare_result'), ['pass' => 'Pass', 'fail' => 'Fail']);
        
        $this->addField("key[text_comparision]", "Text Comparision", '', 'select', Config::getValue('text_comparision'), ['active' => 'Active', 'deactive' => 'Deactive']);
        $this->addField("key[ekyc_strict_comparision]", "Ekyc Strict comparision",  '', 'select', Config::getValue('ekyc_strict_comparision'), ['active' => 'Active', 'deactive' => 'Deactive']);
        //Passowrd secuirtiy
        $this->addField("key[invalid_login_attempts]", "Invalid Login Attemps",'', '', Config::getValue('invalid_login_attempts'));
       
        return $this;
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        foreach ($input['key'] as $key => $value) {
            $config = Config::where('key', $key)->first();
            $config->value = $value;
            $config->save();
        }

        $dayConfig = Config::where('key', 'system_extra_day')->first();
        $daysCounter = $dayConfig->value;
        //        dd(now()->startOfDay()->addDays($dayConfig->value));
        for ($i = 1; $i <= $daysCounter; $i++) {
            $dayConfig->value = $i;
            $dayConfig->save();
            $this->runJobs();
        }
        return redirect(action('Admin\ConfigController@index'));
    }

    public function runJobs()
    {
        TestTime::addDays(Config::getValue('system_extra_day') ?? 0)->addHours(Config::getValue('system_extra_hour') ?? 0);

        DecreaseCoverage::dispatch();
        RenewCoverage::dispatch();
        RetryPendingOrder::dispatch();
        Age::dispatch();
        CancelPendingCoverage::dispatch();
        FulfilledCoverage::dispatch();
        PromoterRefund::dispatch();
        RecalculateCoverage::dispatch();

        TestTime::addDays(((int)Config::getValue('system_extra_day') ?? 0) * -1)->addHours(((int)Config::getValue('system_extra_hour') ?? 0) * -1);
        return true;
    }
}