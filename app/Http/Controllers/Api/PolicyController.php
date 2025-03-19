<?php     

namespace App\Http\Controllers\Api;


use App\Beneficiary;
use App\Coverage;
use App\CoverageType;
use App\CoverageOrder;
use App\Credit;
use App\Events\ChangedCoveragesStatusEvent;
use App\Helpers;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use App\Http\Controllers\Controller;
use App\Individual;
use App\Jobs\GenerateDocument;
use App\Jobs\ProcessPayment;
use App\Notifications\Email;
use App\Order;
use App\UserPdsReview;
use App\IndustryJob;
use App\Thanksgiving;
use App\Transaction;
use App\CoveragePaymentTerm;
use App\SpoCharityFundApplication;
use App\Underwriting;
use App\User;
use App\UserModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;




class PolicyController extends Controller
{

    /**
     * @api {post} api/policies Industry Jobs List
     * @apiVersion 1.0.0
     * @apiName List
     * @apiGroup Policy
     *
     * @apiDescription It shows user's active coverages also coverages that increased. if user has beneficiaries this api show them.
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} claim
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {Array} data[owner]
     * @apiSuccess (Response (200) ) {Array} data[payer]
     * @apiSuccess (Response (200) ) {Array} data[beneficiary]
     *
     * @apiSuccess (Response (200) Modal) {String} status success
     * @apiSuccess (Response (200) Modal) {String} action_type modal
     * @apiSuccess (Response (200) Modal) {Array} modal
     * @apiSuccess (Response (200) Modal) {Array} config
     * @apiSuccess (Response (200) Modal) {Array} data
     * @apiSuccess (Response (200) Modal) {Object} data[user]
     * @apiSuccess (Response (200) Modal) {Boolean} data[is_foreign]
     * @apiSuccess (Response (200) Modal) {Boolean} data[charity_eligible]
     *
     */

    public function getList(Request $request)
    {
        $user = $request->user()->profile;

       

        if($request->input('fill_type') == 'buy_for_others') {
            $userRecord   =   User::AllUsersDTOrNonDT()->whereUuid($request->input('user_id'))->first() ?? '';
            $user         =   $userRecord->profile;
        }

        //check if has decreased status -> only show decreased
        $coverages_payer = $user->coverages_payer()->where("owner_id", "!=", $user->id)->whereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->orderBy('id','asc')->get();

        $coverages_owner = $user->coverages_owner()->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->orderBy('id','asc')->get();

        $coverages_beneficiary = $user->coverages_beneficiary()
									  ->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])
									  ->whereIn('product_name', [Enum::PRODUCT_NAME_DEATH, Enum::PRODUCT_NAME_ACCIDENT])
									  ->orderBy('id','asc')
									  ->get();

        

        if($request->input('claim')){
			$coverages_owner = $user->coverages_owner()->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->whereNotIn('product_name', [Enum::PRODUCT_NAME_DEATH])->orderBy('id','asc')->get();
		}

        if (empty($request->input('claim'))) {
            $coverages_owner = $coverages_owner->merge($coverages_payer);
        }

        $owner = $coverages_owner->groupBy("covered_id")
            ->map(function ($q) use ($user) {
                $covered = $q[0]->covered ?? NULL;
                if (!empty($covered)) {
                    $newArray['covered_selfie'] = $covered->selfie ?? NULL;
                    $newArray['status_id'] = ('' . (empty($covered->user) ? 'pending_registration' : $q[0]->status) . '');
                    $badges = [];
                    if ($covered->id == $user->id && $user->age() > 16)
                        $badges[] = 'i_own';

                    if ($q[0]->payer_id == ($user->user->id ?? 0) && !$user->is_charity())
                        $badges[] = 'i_pay';

                    $newArray['badges'] = $badges;
                    $newArray['covered'] = $covered->name ?? NULL;
                    $newArray['is_corporate_owner_accepted'] = ($q[0]->corporate_user_status == 'accepted') ? true : false;
                    $is_child = ($covered->id ?? 0) != ($q[0]->owner_id ?? -1);
                    $user_uuid = $covered->user()->WithPendingPromoted()->first()->uuid ?? NULL;
                    $newArray['user_id'] = $is_child ? $covered->uuid : ((empty($user_uuid) || $user->user->uuid == $user_uuid) ? NULL : $user_uuid);

                    $term = Coverage::where('owner_id',$user->id)->where('payer_id',$user->user_id)->where('state','active')->first()->payment_term ?? null;

                    $term_new = Coverage::where('owner_id',$user->id)->where('payer_id',$user->user_id)->where('state','active')->first()->payment_term_new ?? null;
                    
                    $newArray['payment_term'] = $term;

                    $newArray['payment_term_new'] = $term_new;
					//$newArray['user_ref_no'] = $covered->user->ref_no ?? NULL;

                    $newArray['is_child'] = $is_child;

                    $coverages = $q->makeHidden(['covered_id'])->groupBy('product_id')->toArray();

                    $cc = [];
                    foreach ($coverages as $i => $coverage) {

                        $is_owner = Arr::where($coverage, function ($ar) use ($user) {
                            $coverage = Coverage::whereUuid($ar['uuid'] ?? 0)->first();

                            return $coverage->owner_id == $user->id;
                        });
                        $coverage_model = Coverage::whereUuid($coverage[0]['uuid'] ?? 0)->first();
                        $is_owner = count($is_owner ?? []) > 0;
                        $o = $coverage[0];
                        $total_cov = 0;
                        $o['d'] = $coverage;
                        foreach ($coverage as $k) {
                            if (request()->input('claim')) {
                                if (($k['state'] ?? '') == Enum::COVERAGE_STATE_ACTIVE || ($k['state'] ?? '') ==Enum::COVERAGE_STATE_DEACTIVATE)
                                if($k['status']=="deactivating" ){
                                    $o['status'] ='Active';
                                }
                                {
                                    if($k['product_name'] == Enum::PRODUCT_NAME_MEDICAL){
                                        $total_cov = $coverage[count($coverage)-1]['coverage'];
                                        $o['real_coverage'] = $coverage[count($coverage)-1]['real_coverage'];
                                        $o['deductible'] = $coverage[count($coverage)-1]['deductible'];
                                    }
                                    else{
                                        $total_cov += $k['coverage'];
                                    }
                                }
                            } else {
                                $seCoverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                                if($seCoverage->status =="deactivating"){
                                    $o['status'] ='Active';
                                    $o['deactivating'] =[
                                        'date' => Carbon::parse($seCoverage->next_payment_on)->format('d/m/Y'),
                                    ];
                                    
                                }
                                if (($seCoverage->state == Enum::COVERAGE_STATE_ACTIVE ||$seCoverage->state == Enum::COVERAGE_STATE_DEACTIVATE ||($seCoverage->payer->id == $user->user_id && $seCoverage->owner->id != $user->id)) && ($seCoverage->payer->id == $user->user_id || $seCoverage->owner->id == $user->id)) {
                                    if ($seCoverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                        $total_cov = $coverage[count($coverage)-1]['coverage'];
                                        $o['real_coverage'] = $coverage[count($coverage)-1]['real_coverage'];
                                        $o['deductible'] = $coverage[count($coverage)-1]['deductible'];
                                    }
                                    else
                                        $total_cov += $seCoverage->coverage;
                                }

                                
                            }
                        }
                        
                        $o['coverage'] = $total_cov;

                        $coveragesnew = Coverage::where('owner_id',$user->id)->where('payer_id',$user->user_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED,Enum::COVERAGE_STATUS_DECREASE_UNPAID])
                        ->get()->filter(function ($item){
                            if($item->next_payment_on!=NULL){
                                $has_decrease =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_DECREASE_UNPAID)->first();
                            if($has_decrease){
                                $has_active = Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                            }else{
                                $has_active =[];
                            }
                            $med_cov =[];
                            if($item->product_id==5){
                                $med_increase =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_ACTIVE_INCREASED)->latest()->first();
                                if($med_increase){
                                    $med_cov =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                                    $med_cov =array_diff($med_cov,[$med_increase->id]);
                                }else{
                                    $med_cov =[];
                                }
                            }
                          
                            if(!in_array($item->id,$med_cov)){
                             if(!in_array($item->id,$has_active)){
                                return $item;
                            }
                        }
                            
                            }
                        });


        $payment_term_unpaid = [];

        if(!empty($coveragesnew)){

        foreach ($coveragesnew as $coverage) {
            $due_date = date('Y-m-d', strtotime($coverage->next_payment_on));
            $due_dates = date('d F Y', strtotime($coverage->next_payment_on));
            $first_pays = date('Y-m-d', strtotime($coverage->first_payment_on));
            $renew = date('Y-m-d', strtotime($coverage->renewal_date)) ?? NULL;
            if ($renew != NULL){
                $renewal =  $renew;
            }else{
                $renewal =  Carbon::parse($coverage->first_payment_on)->addYear();
            }
            $last_pay = date('Y-m-d', strtotime($coverage->last_payment_on)) ?? NULL;

            $productKey = $coverage->product_name;

            if (!isset($payment_term_unpaid[$productKey])) {
                $payment_term_unpaid[$productKey] = [
                    'product_name' => $productKey,
                    'amount_year' => 0,
                    'amount_month' => 0,
                    'due_date' => $due_dates,
                    'amount_balance' => 0,
                    'amount_test'   => 0,
                ];
            }

            $user_c = $user;
    
            $userDob = $user->dob;
            $newAge = Carbon::parse($userDob)->diffInYears($due_date);
            $occ_loading = null;
            $latestuw = Coverage::where('owner_id',$user->id)->where('state',Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->uw_id;
            $underwriting=Underwriting::where('id',$latestuw)->first();
            $quote = $coverage->product->getPrice($user,$coverage->coverage,$occ_loading,$newAge,$coverage->product->name == Enum::PRODUCT_NAME_MEDICAL ? $coverage->deductible : null,$underwriting)[0];

            $annually = round($quote,2);
            $month  = round($coverage->product->covertAnnuallyToMonthly($annually),2);

            $diff_months =date_diff(date_create($renewal), date_create($due_date));
            $diff_monthes =date_diff(date_create($renewal), date_create($first_pays));

            $diff_day = $diff_months->format('%a') / $diff_monthes->format('%a') ;
            $next_payment_to_due_month = $diff_day;

            $amount_balan = round($annually * $next_payment_to_due_month,2);

            $thanks = Thanksgiving::where('individual_id',$user->id)->where('type', 'self')->first()->percentage ?? 0;

            if($thanks != 0) {
            $amount = $amount_balan * $thanks/1000;

            $amount_balance = $amount_balan - $amount;
            $amount_balance = round($amount_balance,2);
            
            }
            else{
                $amount_balance = $amount_balan;
            }

            if($thanks != 0) {
                $amt = $month * $thanks/1000;

                $monthly = $month - $amt;
            }
            else{
                
                $monthly = $month;
                $monthly = round($monthly,2);
            }

            $payment_term_unpaid[$productKey]['amount_test'] += $amount_balance;
            $payment_term_unpaid[$productKey]['amount_balance'] += round($annually,2);
            $payment_term_unpaid[$productKey]['amount_month'] += round($monthly,2);
            $payment_term_unpaid[$productKey]['amount_year'] = round($payment_term_unpaid[$productKey]['amount_test'],2);      
            }
    }
        $newArray['payment_term_unpaid'] = array_values($payment_term_unpaid);

                        if ($is_owner)
                            $docs = [
                                [
                                    "title" => __('mobile.product_disclosure_sheet'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.contract'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $coverage_model->uuid ?? '-1', 'type' => 'contract', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.faq'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                            ];
                        else
                            $docs = [
                                [
                                    "title" => __('mobile.product_disclosure_sheet'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'], 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.faq'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                            ];

                        $o['documents'] = $docs;
                       
                        if ($is_owner){
							$covs = Coverage::where("owner_id", $coverage_model->owner_id)->where("product_id", $coverage_model->product->id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->get();

							$o['user_ref_no'] = $user->user->ref_no;

						}else{
							$covs = Coverage::where("owner_id", $coverage_model->owner_id)->where("product_id", $coverage_model->product->id)->where("payer_id", '=', $user->user_id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->get();

							$o['user_ref_no'] = User::find($user->user_id)->ref_no;
						}

                        $coverage_payers = [];
                        foreach ($covs->groupBy('payer_id') as $payerCoverages) {

                            if($payerCoverages[0]->payment_term == 'monthly'){
                                $premium_amount=round($payerCoverages->sum('payment_monthly'),2);
                            }else{
                                $premium_amount=round($payerCoverages->sum('payment_annually'),2);
                            }
                            $thanksgiving_owner =$payerCoverages[0]->owner->thanksgiving()->get();
                            $premium =Helpers::calcThanksgivingDiscount($thanksgiving_owner,$premium_amount);
                            if($premium ==0){
                                $premium = $premium_amount;
                            }

                            $due_date = Carbon::parse($payerCoverages[0]->next_payment_on)->format('d F Y');
                            $ndd_date_f = $payerCoverages[0]->ndd_payment_due_date ?? null;
                            if($ndd_date_f != null){
                                $ndd_date = Carbon::parse($ndd_date_f)->format('d F Y');
                            }else{
                                $ndd_date = null;
                            }

                           if($user->is_charity()){
                            if($payerCoverages[0]->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                $coverage_payers[] = ['title' => 'DearTime Charity Fund', 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages[count($payerCoverages)-1]->coverage, 'deductible' => $payerCoverages[count($payerCoverages)-1]->deductible, 'premium' =>round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'ndd_payment' =>  $ndd_date,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                            else{
                                $coverage_payers[] = ['title' => 'DearTime Charity Fund', 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages->sum('coverage'), 'deductible' => $payerCoverages[0]->deductible, 'premium' => round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'ndd_payment' =>  $ndd_date,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                           }else{
                            if($payerCoverages[0]->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages[count($payerCoverages)-1]->coverage, 'deductible' => $payerCoverages[count($payerCoverages)-1]->deductible, 'premium' => round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'ndd_payment' =>  $ndd_date,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                            else{
                                $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages->sum('coverage'), 'deductible' => $payerCoverages[0]->deductible, 'premium' => round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'ndd_payment' =>  $ndd_date,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                        }
                        }
                        $o['payers'] = $coverage_payers; 
                        $coverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                        $coverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                        $has_decrease = Coverage::where("product_id", $coverage->product_id)->where("owner_id", $coverage->owner_id)->where("payer_id", $coverage->payer_id)->where("covered_id", $coverage->covered_id)->where("status", Enum::COVERAGE_STATUS_DECREASE_UNPAID)->orderBy('id','desc')->first();
                        if (!empty($has_decrease)) {
                            $o['decrease'] = [
                                'from' => ('RM'.number_format($total_cov) ),
                                'to'   => ('RM'.number_format($has_decrease->coverage) ),
                                'alt'=>Carbon::parse($has_decrease->created_at)->format('d/m/Y'),
                                'date' => Carbon::parse($coverage->next_payment_on)->format('d/m/Y'),
                            ];
                        }
                        if ($total_cov != 0)
                            $cc[] = $o;

                    }


                    $newArray['coverages'] = $cc;

                    return $newArray;
                }

                return NULL;
            })->values()->toArray();
            

        $owner = array_filter($owner);
      

        $coverages_payer_unpaid = $user->coverages_payer()->where("owner_id", "!=", $user->id)
                ->whereIn('status',[Enum::COVERAGE_STATUS_INCREASE_UNPAID, Enum::COVERAGE_STATUS_UNPAID])
                ->orderBy('id','asc')->get();

        $payer_unpaid = $coverages_payer_unpaid->groupBy("covered_id")
            ->map(function ($q) use ($user) {
                $covered = $q[0]->covered ?? NULL;
                if (!empty($covered)) {
                    $newArray['covered_selfie'] = $covered->selfie ?? NULL;
                    $newArray['status_id'] = ('' . (empty($covered->user) ? 'pending_registration' : $q[0]->status) . '');
                    $badges = ['policy_i_offer'];

                    $newArray['badges'] = $badges;
                    $newArray['covered'] = $covered->name ?? NULL;
                    $newArray['covered_payername'] = $newArray['covered'];
                    $newArray['is_corporate_owner_accepted'] = ($q[0]->corporate_user_status == 'accepted') ? true : false;
                    $is_child = ($covered->id ?? 0) != ($q[0]->owner_id ?? -1);
                    $user_uuid = $covered->user()->WithPendingPromoted()->first()->uuid ?? NULL;
                    $newArray['user_id'] = $is_child ? $covered->uuid : ((empty($user_uuid) || $user->user->uuid == $user_uuid) ? NULL : $user_uuid);

					//$newArray['user_ref_no'] = $covered->user->ref_no ?? NULL;

                    $newArray['is_child'] = $is_child;

                    $coverages = $q->makeHidden(['covered_id'])->groupBy('product_id')->toArray();

                    $cc = [];
                    foreach ($coverages as $i => $coverage) {

                        $is_owner = Arr::where($coverage, function ($ar) use ($user) {
                            $coverage = Coverage::whereUuid($ar['uuid'] ?? 0)->first();

                            return $coverage->owner_id == $user->id;
                        });
                        $coverage_model = Coverage::whereUuid($coverage[0]['uuid'] ?? 0)->first();
                        $is_owner = count($is_owner ?? []) > 0;
                        $o = $coverage[0];
                        $total_cov = 0;
                        $o['d'] = $coverage;
                        foreach ($coverage as $k) {
                            if (request()->input('claim')) {
                                if (($k['state'] ?? '') == Enum::COVERAGE_STATE_ACTIVE || ($k['state'] ?? '') ==Enum::COVERAGE_STATE_DEACTIVATE)
                                if($k['status']=="deactivating" ){
                                    $o['status'] ='Active';
                                }
                                {
                                    if($k['product_name'] == Enum::PRODUCT_NAME_MEDICAL){
                                        $total_cov = $coverage[count($coverage)-1]['coverage'];
                                        $o['real_coverage'] = $coverage[count($coverage)-1]['real_coverage'];
                                        $o['deductible'] = $coverage[count($coverage)-1]['deductible'];
                                    }
                                    else{
                                        $total_cov += $k['coverage'];
                                    }
                                }
                            } else {
                                $seCoverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                                if($seCoverage->status =="deactivating"){
                                    $o['status'] ='Active';
                                }
                                if (($seCoverage->state == Enum::COVERAGE_STATE_INACTIVE ||($seCoverage->payer->id == $user->user_id && $seCoverage->owner->id != $user->id)) && ($seCoverage->payer->id == $user->user_id || $seCoverage->owner->id == $user->id)) {
                                    if ($seCoverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                        $total_cov = $coverage[count($coverage)-1]['coverage'];
                                        $o['real_coverage'] = $coverage[count($coverage)-1]['real_coverage'];
                                        $o['deductible'] = $coverage[count($coverage)-1]['deductible'];
                                    }
                                    else
                                        $total_cov += $seCoverage->coverage;
                                }
                            }
                        }
                        
                        $o['coverage'] = $total_cov;

                        if ($is_owner)
                            $docs = [
                                [
                                    "title" => __('mobile.product_disclosure_sheet'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.contract'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $coverage_model->uuid ?? '-1', 'type' => 'contract', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.faq'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                            ];
                        else
                            $docs = [
                                [
                                    "title" => __('mobile.product_disclosure_sheet'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'], 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.faq'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                            ];

                        $o['documents'] = $docs;
                       
                        if ($is_owner){
							$covs = Coverage::where("owner_id", $coverage_model->owner_id)->where("product_id", $coverage_model->product->id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->get();

							$o['user_ref_no'] = $user->user->ref_no;

						}else{
							$covs = Coverage::where("owner_id", $coverage_model->owner_id)->where("product_id", $coverage_model->product->id)->where("payer_id", '=', $user->user_id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->get();

							$o['user_ref_no'] = User::find($user->user_id)->ref_no;
						}

                        $coverage_payers = [];
                        foreach ($covs->groupBy('payer_id') as $payerCoverages) {
                        if($payerCoverages[0]->payment_term == 'monthly'){
                                $premium_amount=round($payerCoverages->sum('payment_monthly'),2);
                            }else{
                                $premium_amount=round($payerCoverages->sum('payment_annually'),2);
                            }
                            $thanksgiving_owner =$payerCoverages[0]->owner->thanksgiving()->get();
                            $premium =Helpers::calcThanksgivingDiscount($thanksgiving_owner,$premium_amount);
                            if($premium ==0){
                                $premium = $premium_amount;
                            }
                            $due_date = Carbon::parse($payerCoverages[0]->next_payment_on)->format('d F Y');
                           if($user->is_charity()){
                            if($payerCoverages[0]->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                $coverage_payers[] = ['title' => 'DearTime Charity Fund', 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages[count($payerCoverages)-1]->coverage, 'deductible' => $payerCoverages[count($payerCoverages)-1]->deductible, 'premium' => round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                            else{
                                $coverage_payers[] = ['title' => 'DearTime Charity Fund', 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages->sum('coverage'), 'deductible' => $payerCoverages[0]->deductible, 'premium' => round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                           }else{
                            if($payerCoverages[0]->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages[count($payerCoverages)-1]->coverage, 'deductible' => $payerCoverages[count($payerCoverages)-1]->deductible, 'premium' => round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                            else{
                                $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages->sum('coverage'), 'deductible' => $payerCoverages[0]->deductible, 'premium' =>round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                        }
                        }
                        $o['payers'] = $coverage_payers; 
                        $coverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                        $coverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                        $has_decrease = Coverage::where("product_id", $coverage->product_id)->where("owner_id", $coverage->owner_id)->where("payer_id", $coverage->payer_id)->where("covered_id", $coverage->covered_id)->where("status", Enum::COVERAGE_STATUS_DECREASE_UNPAID)->orderBy('id','desc')->first();
                        if (!empty($has_decrease)) {
                            $o['decrease'] = [
                                'from' => ('RM'.number_format($total_cov) ),
                                'to'   => ('RM'.number_format($has_decrease->coverage) ),
                                'alt'=>Carbon::parse($has_decrease->created_at)->format('d/m/Y'),
                                'date' => Carbon::parse($coverage->next_payment_on)->format('d/m/Y'),
                            ];
                        }
                        if ($total_cov != 0)
                            $cc[] = $o;

                    }


                    $newArray['coverages'] = $cc;

                    return $newArray;
                }

                return NULL;
            })->values()->toArray();

        $payer_unpaid = array_filter($payer_unpaid);

        $coverages_owner_unpaid = $user->coverages_owner()
                                ->where('payer_id', '<>', $user->user_id)
                                ->whereIn('status',[Enum::COVERAGE_STATUS_INCREASE_UNPAID, Enum::COVERAGE_STATUS_UNPAID])
                                ->orderBy('id','asc')->get();

        $owner_unpaid = $coverages_owner_unpaid->groupBy("payer_id")
            ->map(function ($q) use ($user) {
                $covered = $q[0]->covered ?? NULL;
                if (!empty($covered)) {
                    $newArray['covered_selfie'] = $covered->selfie ?? NULL;
                    $newArray['status_id'] = ('' . (empty($covered->user) ? 'pending_registration' : $q[0]->status) . '');
                    $badges = ['policy_offer'];

                    $newArray['badges'] = $badges;
                    $newArray['covered'] = $covered->name ?? NULL;
                    $newArray['covered_payername'] = $q[0]->payer->profile->name ?? NULL;
                    $newArray['covered_payer_id'] = $q[0]->payer->profile->user_id ?? NULL;
                    $newArray['is_corporate_owner_accepted'] = ($q[0]->corporate_user_status == 'accepted') ? true : false;
                    $is_child = ($covered->id ?? 0) != ($q[0]->owner_id ?? -1);
                    $user_uuid = $covered->user()->WithPendingPromoted()->first()->uuid ?? NULL;
                    $newArray['user_id'] = $is_child ? $covered->uuid : ((empty($user_uuid) || $user->user->uuid == $user_uuid) ? NULL : $user_uuid);

					//$newArray['user_ref_no'] = $covered->user->ref_no ?? NULL;

                    $newArray['is_child'] = $is_child;

                    $coverages = $q->makeHidden(['covered_id'])->groupBy('product_id')->toArray();

                    $cc = [];
                    foreach ($coverages as $i => $coverage) {

                        $is_owner = Arr::where($coverage, function ($ar) use ($user) {
                            $coverage = Coverage::whereUuid($ar['uuid'] ?? 0)->first();

                            return $coverage->owner_id == $user->id;
                        });
                        $coverage_model = Coverage::whereUuid($coverage[0]['uuid'] ?? 0)->first();
                        $is_owner = count($is_owner ?? []) > 0;
                        $o = $coverage[0];
                        $total_cov = 0;
                        $o['d'] = $coverage;
                        foreach ($coverage as $k) {
                            if (request()->input('claim')) {
                                if (($k['state'] ?? '') == Enum::COVERAGE_STATE_ACTIVE || ($k['state'] ?? '') ==Enum::COVERAGE_STATE_DEACTIVATE)
                                if($k['status']=="deactivating" ){
                                    $o['status'] ='Active';
                                }
                                {
                                    if($k['product_name'] == Enum::PRODUCT_NAME_MEDICAL){
                                        $total_cov = $coverage[count($coverage)-1]['coverage'];
                                        $o['real_coverage'] = $coverage[count($coverage)-1]['real_coverage'];
                                        $o['deductible'] = $coverage[count($coverage)-1]['deductible'];
                                    }
                                    else{
                                        $total_cov += $k['coverage'];
                                    }
                                }
                            } else {
                                $seCoverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                                if($seCoverage->status =="deactivating"){
                                    $o['status'] ='Active';
                                }
                                if (($seCoverage->state == Enum::COVERAGE_STATE_INACTIVE ||($seCoverage->payer->id == $user->user_id && $seCoverage->owner->id != $user->id)) && ($seCoverage->payer->id == $user->user_id || $seCoverage->owner->id == $user->id)) {
                                    if ($seCoverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                        $total_cov = $coverage[count($coverage)-1]['coverage'];
                                        $o['real_coverage'] = $coverage[count($coverage)-1]['real_coverage'];
                                        $o['deductible'] = $coverage[count($coverage)-1]['deductible'];
                                    }
                                    else
                                        $total_cov += $seCoverage->coverage;
                                }
                            }
                        }
                        
                        $o['coverage'] = $total_cov;

                        if ($is_owner)
                            $docs = [
                                [
                                    "title" => __('mobile.product_disclosure_sheet'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.contract'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $coverage_model->uuid ?? '-1', 'type' => 'contract', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.faq'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                            ];
                        else
                            $docs = [
                                [
                                    "title" => __('mobile.product_disclosure_sheet'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'], 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.faq'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                            ];

                        $o['documents'] = $docs;
                       
                        if ($is_owner){
							$covs = Coverage::where("owner_id", $coverage_model->owner_id)->where("product_id", $coverage_model->product->id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->get();

							$o['user_ref_no'] = $user->user->ref_no;

						}else{
							$covs = Coverage::where("owner_id", $coverage_model->owner_id)->where("product_id", $coverage_model->product->id)->where("payer_id", '=', $user->user_id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->get();

							$o['user_ref_no'] = User::find($user->user_id)->ref_no;
						}

                        $coverage_payers = [];
                        foreach ($covs->groupBy('payer_id') as $payerCoverages) {

                        if($payerCoverages[0]->payment_term == 'monthly'){
                                $premium_amount=round($payerCoverages->sum('payment_monthly'),2);
                            }else{
                                $premium_amount=round($payerCoverages->sum('payment_annually'),2);
                            }
                            $thanksgiving_owner =$payerCoverages[0]->owner->thanksgiving()->get();
                            $premium =Helpers::calcThanksgivingDiscount($thanksgiving_owner,$premium_amount);
                            if($premium ==0){
                                $premium = $premium_amount;
                            }

                            $due_date = Carbon::parse($payerCoverages[0]->next_payment_on)->format('d F Y');
                            if($payerCoverages[0]->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages[count($payerCoverages)-1]->coverage, 'deductible' => $payerCoverages[count($payerCoverages)-1]->deductible, 'premium' => round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                            else{
                                $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages->sum('coverage'), 'deductible' => $payerCoverages[0]->deductible, 'premium' => round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'annual_limit'=>config('static.medical_annual_limit')];
                            }
                        }
                        $o['payers'] = $coverage_payers;

                        $coverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                        $coverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                        $has_decrease = Coverage::where("product_id", $coverage->product_id)->where("owner_id", $coverage->owner_id)->where("payer_id", $coverage->payer_id)->where("covered_id", $coverage->covered_id)->where("status", Enum::COVERAGE_STATUS_DECREASE_UNPAID)->orderBy('id','desc')->first();
                        if (!empty($has_decrease)) {
                            $o['decrease'] = [
                                'from' => ('RM'.number_format($total_cov) ),
                                'to'   => ('RM'.number_format($has_decrease->coverage) ),
                                'alt'=>Carbon::parse($has_decrease->created_at)->format('d/m/Y'),
                                'date' => Carbon::parse($coverage->next_payment_on)->format('d/m/Y'),
                            ];
                        }
                        if ($total_cov != 0)
                            $cc[] = $o;

                    }


                    $newArray['coverages'] = $cc;

                    return $newArray;
                }

                return NULL;
            })->values()->toArray();

        $owner_unpaid = array_filter($owner_unpaid);

        $beneficiary = $coverages_beneficiary->groupBy("covered_id")
            ->map(function ($q) use ($user) {
                $covered = $q[0]->covered ?? NULL;

                if (!empty($covered)) {
                    if($covered->status == 'deactivating'){
                        $covered->status ='active';
                    }

                    $newArray['covered_selfie'] = $covered->selfie ?? NULL;
                    $newArray['status_id'] = ('' . (empty($covered->user) ? 'Pending Registration' : $q[0]->status) . '');
                    $badges = [];
                    if ($covered->id == $user->id && $user->age() > 16)
                        $badges[] = 'i_own';

                    if ($q[0]->payer_id == ($user->user->id ?? 0) && !$user->is_charity())
                        $badges[] = 'i_pay';


                    $newArray['badges'] = $badges;
                    $newArray['covered'] = $covered->name;
                    $is_child = ($covered->id ?? 0) != ($q[0]->owner_id ?? -1);
                    $user_uuid = $covered->user()->WithPendingPromoted()->first()->uuid ?? NULL;
                    $newArray['user_id'] = $is_child ? $covered->uuid : ((empty($user_uuid) || $user->user->uuid == $user_uuid) ? NULL : $user_uuid);
                    $newArray['is_child'] = $is_child;

                    $percentage = Beneficiary::where('individual_id', $covered->id)->where('nominee_id', $user->id)->first()->percentage;

                    $coverages = $q->makeHidden(['covered_id'])->groupBy('product_id')->toArray();
                    $cc = [];
                    foreach ($coverages as $i => $coverage) {

                        $is_owner = Arr::where($coverage, function ($ar) use ($user) {
                            $coverage = Coverage::whereUuid($ar['uuid'] ?? 0)->first();
                            if($coverage->status == 'deactivating'){
                                $coverage->status ='active';
                            }
                            return $coverage->owner_id == $user->id;
                        });

                       
                        $coverage_model = Coverage::whereUuid($coverage[0]['uuid'] ?? 0)->first();
                        $is_owner = count($is_owner ?? []) > 0;
                        $o = $coverage[0];
                        $total_cov = 0;

                        foreach ($coverage as $k) {
                            if (($k['status'] ?? '') == Enum::COVERAGE_STATUS_ACTIVE)
                                $total_cov += $k['coverage'];

                           

                            
                        }
                        $o['coverage'] = $total_cov * ($percentage /100) ;
                        if($o['status'] =='deactivating'){
                            $o['status'] ='Active';
                        }
                        if ($is_owner)
                            $docs = [
                                [
                                    "title" => __('mobile.product_disclosure_sheet'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.contract'),
                                    "link" => 'https://docs.google.com/viewer?url=http://dev.deartime.com/documents/Contract-Death.pdf',
                                    "type" => "pdf"
                                ],
                                [
                                    "title" => __('mobile.faq'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                            ];
                        else
                            $docs = [
                                [
                                    "title" => __('mobile.product_disclosure_sheet'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'], 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"

                                ],
                                [
                                    "title" => __('mobile.faq'),
                                    "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                                    "type" => "pdf"
                                ],
                            ];

                        $o['documents'] = $docs;

                        if ($is_owner)
                            $covs = Coverage::where("owner_id", $user->id)->where("product_id", $coverage_model->product->id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->get();
                        else
                            $covs = Coverage::where("owner_id", $user->id)->where("product_id", $coverage_model->product->id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->where("payer_id", '=', $user->user_id)->get();

                        $coverage_payers = [];
                        foreach ($covs->groupBy('payer_id') as $payerCoverages) {
                            if($payerCoverages[0]->payment_term == 'monthly'){
                                $premium_amount=round($payerCoverages->sum('payment_monthly'),2);
                            }else{
                                $premium_amount=round($payerCoverages->sum('payment_annually'),2);
                            }
                            $thanksgiving_owner =$payerCoverages[0]->owner->thanksgiving()->get();
                            $premium =Helpers::calcThanksgivingDiscount($thanksgiving_owner,$premium_amount);
                            if($premium ==0){
                                $premium = $premium_amount;
                            }
                            $due_date = Carbon::parse($payerCoverages[0]->next_payment_on)->format('d F Y');
                            if($payerCoverages[0]->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages[count($payerCoverages)-1]->coverage, 'deductible' => $payerCoverages[count($payerCoverages)-1]->deductible, 'premium' => $premium, 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term];
                            }
                            else{
                                $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages->sum('coverage'), 'deductible' => $payerCoverages[0]->deductible, 'premium' => $premium, 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term];
                            }
                        }
                        $o['payers'] = $coverage_payers;
                        $cc[] = $o;

                    }


                    $newArray['coverages'] = $cc;

                    return $newArray;
                }
                return NULL;

            })->values()->toArray();

        $beneficiary = array_filter($beneficiary);

        $charity_approved =false;
        $charity_application =SpoCharityFundApplication::where('user_id',$user->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
        if($charity_application){
            if($charity_application->status =='ACTIVE'){
                $charity_approved =true;
            }
        }

        $coverages = Coverage::where('owner_id',$user->id)->where('payer_id',$user->user_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED,Enum::COVERAGE_STATUS_DECREASE_UNPAID])
        ->get()->filter(function ($item){
            if($item->next_payment_on!=NULL){
                $has_decrease =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_DECREASE_UNPAID)->first();
            if($has_decrease){
                $has_active = Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
            }else{
                $has_active =[];
            }
            $med_cov =[];
            if($item->product_id==5){
                $med_increase =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_ACTIVE_INCREASED)->latest()->first();
                if($med_increase){
                    $med_cov =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                    $med_cov =array_diff($med_cov,[$med_increase->id]);
                }else{
                    $med_cov =[];
                }
            }
          
            if(!in_array($item->id,$med_cov)){
             if(!in_array($item->id,$has_active)){
                return $item;
            }
        }
            
            }
        });

                    $user_c = $user;

                    if(!empty($coverages)){

                        foreach ($coverages as $ys) {
                            $due_dates = date('Y-m-d', strtotime($ys->next_payment_on));
                            $first_pays = date('Y-m-d', strtotime($ys->first_payment_on));
                            $renew = date('Y-m-d', strtotime($ys->renewal_date)) ?? NULL;
                            if ($renew != NULL){
                                $renewal =  $renew;
                            }else{
                                $renewal =  Carbon::parse($ys->first_payment_on)->addYear();
                            }
                            $last_pay = date('Y-m-d', strtotime($ys->last_payment_on)) ?? NULL;
                    
                            $userDob = $user->dob;
                            $newAge = Carbon::parse($userDob)->diffInYears($due_dates);
                            $occ_loading = null;
                            $latestuw = Coverage::where('owner_id',$user->id)->where('state',Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->uw_id;
                            $underwriting=Underwriting::where('id',$latestuw)->first();
                            $teste = $ys->product->getPrice($user,$ys->coverage,$occ_loading,$newAge,$ys->product->name == Enum::PRODUCT_NAME_MEDICAL ? $ys->deductible : null,$underwriting)[0];

                            $annually = round($teste,2);
                            $month  = round($ys->product->covertAnnuallyToMonthly($annually),2);

                             $diff_months =date_diff(date_create($renewal), date_create($due_dates));
                             $diff_monthes =date_diff(date_create($renewal), date_create($first_pays));

                             $diff_day = $diff_months->format('%a') / $diff_monthes->format('%a') ;
                             $next_payment_to_due_month = $diff_day;
                             
                             $amount_balan = round($annually * $next_payment_to_due_month,2);

                             $thanks = Thanksgiving::where('individual_id',$user->id)->where('type', 'self')->first()->percentage ?? 0;
                 
                             if($thanks != 0) {
                             $amount = $amount_balan * $thanks/1000;
                 
                             $amount_balance = $amount_balan - $amount;

                             $amount_balance = round($amount_balance,2);

                             }
                             else{
                                 $amount_balance = $amount_balan;
                             }

                             if($thanks != 0) {
                                $amt = $month * $thanks/1000;
                
                                $monthly = $month - $amt;

                                $monthly = round($monthly,2);
                            }
                            else{
                                
                                $monthly = $month;
                            }

                        
                            $data_1[] = [
                                'product_name' => $ys->product_name,
                                'amount_year' =>$annually,
                                'amount_month' =>$monthly,
                                'due_date' => $due_dates,
                                'first_pay' => $first_pays,
                                'next_payment_to_due_month' => $next_payment_to_due_month,
                                'amount_p'    => $amount_balance,
                            ];
                        }
                
                    $te = 0;
                    if(!empty($data_1)){
                    foreach ($data_1 as $item) {
                        $te += round($item['amount_p'],2);
                    }
                }
                
                    $tee = 0;
                    if(!empty($data_1)){
                    foreach ($data_1 as $items) {
                        $tee += round($items['amount_year'],2);
                    }
                    }

                    $tes = 0;
                    if(!empty($data_1)){
                    foreach ($data_1 as $items) {
                        $tes += round($items['amount_month'],2);
                    }

                }
            }

            $coveragety = CoveragePaymentTerm::where('owner_id',$user->id)->where('pay_term','annually')->latest()->first()->created_at ?? null;

            $coveraget = CoveragePaymentTerm::where('owner_id',$user->id)->where('pay_term','monthly')->latest()->first()->created_at ?? null;

            if($coveraget != null){

            $changes_date_monthly = Carbon::parse( $coveraget)->format('d/m/Y') ?? null;
            }
            else{
                $changes_date_monthly = null;
            }

            if($coveragety != null){

            $changes_date_annually = Carbon::parse( $coveragety)->format('d/m/Y') ?? null;

            }else{

                $changes_date_annually = null;
            }
    
    
    if ($request->input('user_id')!= 'null' && $request->input('user_id')!= null && !empty($request) ){ 
            
        $coverages_payor_owner_unpaid = Coverage::where('owner_id',$user->id)
            ->where('payer_id', $user->user_id)
            ->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID])
            ->orderBy('id','asc')->get();
       
        $owner_payor_unpaid = $coverages_payor_owner_unpaid->groupBy("payer_id")
        ->map(function ($q) use ($user) {
        $covered = $q[0]->covered ?? NULL;
       
        if (!empty($covered)) {
            $newArray['covered_selfie'] = $covered->selfie ?? NULL;
            $newArray['status_id'] = ('' . (empty($covered->user) ? 'pending_registration' : $q[0]->status) . '');
            $badges = [__('mobile.own')];
            $newArray['badges'] = $badges;
            $newArray['covered'] = $covered->name ?? NULL;
            $newArray['covered_payername'] = $q[0]->payer->profile->name ?? NULL;
            $newArray['covered_payer_id'] = $q[0]->payer->profile->user_id ?? NULL;
            $newArray['is_corporate_owner_accepted'] = ($q[0]->corporate_user_status == 'accepted') ? true : false;
            $is_child = ($covered->id ?? 0) != ($q[0]->owner_id ?? -1);
            $user_uuid = $covered->user()->WithPendingPromoted()->first()->uuid ?? NULL;
            $newArray['user_id'] = $is_child ? $covered->uuid : ((empty($user_uuid) || $user->user->uuid == $user_uuid) ? NULL : $user_uuid);
            //$newArray['user_ref_no'] = $covered->user->ref_no ?? NULL;
            $newArray['is_child'] = $is_child;
            $coverages = $q->makeHidden(['covered_id'])->groupBy('product_id')->toArray();
           
            $cc = [];
        
        foreach ($coverages as $i => $coverage) {
                $is_owner = Arr::where($coverage, function ($ar) use ($user) {
                    $coverage = Coverage::whereUuid($ar['uuid'] ?? 0)->first();
                    return $coverage->owner_id == $user->id;
                });

                $coverage_model = Coverage::whereUuid($coverage[0]['uuid'] ?? 0)->first();
                $is_owner = count($is_owner ?? []) > 0;
                $o = $coverage[0];
                $total_cov = 0;
                $o['d'] = $coverage;

                foreach ($coverage as $k) {
                    if (request()->input('claim')) {
                        if (($k['state'] ?? '') == Enum::COVERAGE_STATE_ACTIVE || ($k['state'] ?? '') ==Enum::COVERAGE_STATE_DEACTIVATE)
                        if($k['status']=="deactivating" ){
                            $o['status'] ='Active';
                        }
                        {
                            if($k['product_name'] == Enum::PRODUCT_NAME_MEDICAL){
                                $total_cov = $coverage[count($coverage)-1]['coverage'];
                                $o['real_coverage'] = $coverage[count($coverage)-1]['real_coverage'];
                                $o['deductible'] = $coverage[count($coverage)-1]['deductible'];
                            }
                            else{
                                $total_cov += $k['coverage'];
                            }
                        }
                    } else {
                        $seCoverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                        if($seCoverage->status =="deactivating"){
                            $o['status'] ='Active';
                        }
                        if (($seCoverage->state == Enum::COVERAGE_STATE_INACTIVE ||($seCoverage->payer->id == $user->user_id && $seCoverage->owner->id != $user->id)) && ($seCoverage->payer->id == $user->user_id || $seCoverage->owner->id == $user->id)) {
                            if ($seCoverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                                $total_cov = $coverage[count($coverage)-1]['coverage'];
                                $o['real_coverage'] = $coverage[count($coverage)-1]['real_coverage'];
                                $o['deductible'] = $coverage[count($coverage)-1]['deductible'];
                            }
                            else
                                $total_cov += $seCoverage->coverage;
                        }
                    }
                }
                
            $o['coverage'] = $total_cov;
           
            if ($is_owner)

                    $docs = [
                        [
                            "title" => __('mobile.product_disclosure_sheet'),
                            "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                            "type" => "pdf"
                        ],
                        [
                            "title" => __('mobile.contract'),
                            "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $coverage_model->uuid ?? '-1', 'type' => 'contract', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                            "type" => "pdf"
                        ],
                        [
                            "title" => __('mobile.faq'),
                            "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                            "type" => "pdf"
                        ],
                    ];
                else
                    $docs = [
                        [
                            "title" => __('mobile.product_disclosure_sheet'),
                            "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'pds', 'p' => $o['product_name'], 'uuid' => encrypt($coverage_model->covered->user_id)]),
                            "type" => "pdf"
                        ],
                        [
                            "title" => __('mobile.faq'),
                            "link" => route('doc.view', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $o['product_name']==Enum::PRODUCT_NAME_MEDICAL?$o['real_coverage']:$o['coverage'] ?? 2000, 'term' => $o['payment_term'] == 'monthly' ? 'monthly' : 'annually', 'type' => 'faq', 'p' => $o['product_name'] ?? '', 'uuid' => encrypt($coverage_model->covered->user_id)]),
                            "type" => "pdf"
                        ],
                    ];

                $o['documents'] = $docs;
               
                if ($is_owner){
                    $covs = Coverage::where("owner_id", $coverage_model->owner_id)->where("product_id", $coverage_model->product->id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->get();
                    $o['user_ref_no'] = $user->user->ref_no;
                }else{
                    $covs = Coverage::where("owner_id", $coverage_model->owner_id)->where("product_id", $coverage_model->product->id)->where("payer_id", '=', $user->user_id)->WhereIn('state',[Enum::COVERAGE_STATE_ACTIVE,Enum::COVERAGE_STATE_DEACTIVATE])->get();
                    $o['user_ref_no'] = User::find($user->user_id)->ref_no;
                }

            $coverage_payers = [];
                foreach ($covs->groupBy('payer_id') as $payerCoverages) {

                    if($payerCoverages[0]->payment_term == 'monthly'){
                        $premium_amount=round($payerCoverages->sum('payment_monthly'),2);
                    }else{
                        $premium_amount=round($payerCoverages->sum('payment_annually'),2);
                    }
                    $thanksgiving_owner =$payerCoverages[0]->owner->thanksgiving()->get();
                    $premium =Helpers::calcThanksgivingDiscount($thanksgiving_owner,$premium_amount);
                    if($premium == 0){
                        $premium = $premium_amount;
                    }
                    $due_date = Carbon::parse($payerCoverages[0]->next_payment_on)->format('d F Y');
                    if($payerCoverages[0]->product_name == Enum::PRODUCT_NAME_MEDICAL){
                        $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages[count($payerCoverages)-1]->coverage, 'deductible' => $payerCoverages[count($payerCoverages)-1]->deductible, 'premium' => round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'annual_limit'=>config('static.medical_annual_limit')];
                    }
                    else{
                        $coverage_payers[] = ['title' => ($payerCoverages[0]->payer->name ?? '-'), 'id' => $payerCoverages[0]->uuid, 'coverage' => $payerCoverages->sum('coverage'), 'deductible' => $payerCoverages[0]->deductible, 'premium' =>  round($premium,2), 'due_date' => $due_date, 'payment_term' => $payerCoverages[0]->payment_term,'annual_limit'=>config('static.medical_annual_limit')];
                    }
                }
                $o['payers'] = $coverage_payers;
                $coverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                $coverage = Coverage::whereUuid($k['uuid'] ?? 0)->first();
                $has_decrease = Coverage::where("product_id", $coverage->product_id)->where("owner_id", $coverage->owner_id)->where("payer_id", $coverage->payer_id)->where("covered_id", $coverage->covered_id)->where("status", Enum::COVERAGE_STATUS_DECREASE_UNPAID)->orderBy('id','desc')->first();
                if (!empty($has_decrease)) {
                    $o['decrease'] = [
                        'from' => ('RM'.number_format($total_cov) ),
                        'to'   => ('RM'.number_format($has_decrease->coverage) ),
                        'alt'=>Carbon::parse($has_decrease->created_at)->format('d/m/Y'),
                        'date' => Carbon::parse($coverage->next_payment_on)->format('d/m/Y'),
                    ];
                }
                if ($total_cov != 0)
                    $cc[] = $o;
            }

            $newArray['coverages'] = $cc;
            return $newArray;
        }
        return NULL;

        })->values()->toArray();

        $owner_payor_unpaid = array_filter($owner_payor_unpaid);

        }

        //corporate_age_occ_check
        //$ownedcoverages =$user->coverages_owner()->get();
        //if($ownedcoverages->isNotEmpty()){
        //foreach($ownedcoverages as $ownedcoverage){
            //if($ownedcoverage->payer->corporate_type=='payorcorporate'){
                //$this->corporate_ageocccheck($user,$ownedcoverage->product_id,$ownedcoverage);
            //}
        //}
    //}

        //todo uncomment this lines after launch referrer and pay for others
//        if ($user->isOld() || !$user->is_local()) {
//            $modal = [
//                "body" => __('mobile.age_above_65_or_unlocal'),
//                "buttons" => [
//                    [
//                        "title" => __('mobile.buy_for_others'),
//                        "action" => NextPage::ADD_NOMINEE,
//                        "type" => "page",
//                    ],
//                    [
//                        "title" => __('mobile.invite'),
//                        "action" => "",
//                        "type" => "",
//                    ]
//                ]
//            ];
//            return Helpers::response('success', Enum::PAGE_ACTION_TYPE_MODAL, $modal);
//        }

//return['user'=>  $owner_payor_unpaid];

        return [
            'status' => 'success',
            'data' => [
                'charity'=>$user->is_charity(), 
                'charity_approved'=>$charity_approved,
                'owner' => array_values($owner),
                'payer' => [],
                'beneficiary' => array_values($beneficiary),
                 'payer_unpaid' => $payer_unpaid,
                 'owner_unpaid' => $owner_unpaid,
                'annual_b'    =>round($te,2) ?? '',
                'annual'      =>round($tee,2) ?? '',
                'month'       => round($tes,2) ?? '',
                'changes_date_monthly'  =>$changes_date_monthly ?? '',
                'changes_date_annually' => $changes_date_annually ?? '',
                'owner_payor_unpaid'          =>$owner_payor_unpaid ?? [],

            ]
        ];

    }

    /**
     * @api {post} api/orderReview order process
     * @apiVersion 1.0.0
     * @apiName OrderProcess (Payment)
     * @apiGroup Policy
     *
     * @apiDescription It gets user's payment pending coverages and call payment process
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} fill_type empty or pay_for_others
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {Array} data[order_confirmation]
     * @apiSuccess (Response (200) ) {String} data[next_page]
     *
     * @apiSuccess (Response (200) Modal) {String} status success
     * @apiSuccess (Response (200) Modal) {String} action_type modal
     * @apiSuccess (Response (200) Modal) {Array} modal
     * @apiSuccess (Response (200) Modal) {Array} config
     * @apiSuccess (Response (200) Modal) {Array} data
     * @apiSuccess (Response (200) Modal) {Object} data[user]
     * @apiSuccess (Response (200) Modal) {Boolean} data[is_foreign]
     * @apiSuccess (Response (200) Modal) {Boolean} data[charity_eligible]
     *
     * @apiError  {String} status error
     * @apiError  {String} message
     *
     *
     */

    public function orderProcess(Request $request)
    {
        $out = $this->orderReview($request);
        //dd($out);
        $user = $request->user();
        if (empty($user))
            $user = \auth('web')->user();

        if ($out['status'] != 'success')
            return $out;

        $out = $out['data'] ?? [];

        $cov_names = [];
        $allow_payer_prompt =false;

        foreach ($out['coverages'] ?? [] as $item) {
            $cov_names[] = $item['product_name'];
            if($item['status']!= Enum::COVERAGE_STATUS_DECREASE_UNPAID){
                $allow_payer_prompt =true;
            }
        }

        if ($request->input('fill_type') == 'pay_for_others') {


            //check if accepted from payer
            $user_coverage = $out['coverages'][0]['uuid'] ?? NULL;
            if (empty($user_coverage))
                abort(400);


            $user_coverage = Coverage::whereUuid($user_coverage)->first();

            //check if the child
            $covered = Individual::withChild()->findOrFail($user_coverage->covered_id);
            if ($covered->isChild()) {
                if ($covered->owner->id != $user->profile->id)
                    abort(400);

            } else {

                if ($user_coverage->is_accepted_by_owner != '1' && $allow_payer_prompt) {
                    //send notification
                    //not verified by owner
                    if (empty($user_coverage->owner->user->password)) {       //NON DT USER
                        $emailText = __('mobile.payor_owner_agreement_non_dtuser', [
                            'payer_name' => $user_coverage->payer->name,
                            'owner_name' => $user_coverage->owner->name,
                        ]);
                    }
                    else {
                        $emailText = __('mobile.payor_owner_agreement', [
                            'payer_name' => $user_coverage->payer->name,
                            'owner_name' => $user_coverage->owner->name,
                        ]);
                    }
                    $user_coverage->owner->user->sendNotification('mobile.payor_owner_agreement_title','mobile.payor_owner_agreement_content',
                        [
                            'command' => 'next_page',
                            'translate_data' => [
                                'payer_name' => $user_coverage->payer->name,
                                'owner_name' => $user_coverage->owner->name,
                                'coverages' => implode(" , ", $cov_names)
                            ],
                            'page_data' => [
                                'fill_type' => 'pay_for_others',
                                'payer_id' => $user_coverage->payer->uuid,
                                'user_id' => $user_coverage->covered->uuid ?? 0
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
                        ]);

                    $user_coverage->owner->user->notify(new \App\Notifications\Email($emailText, ['subject' => __('mobile.payor_owner_agreement_title')]));

                     return ['status' => 'success', 'data' => [
                            'next_page'=>'order_receipt_page',
                            ]];
                            
                   // $modal = [
                    //     "body"    => __('web/product.payer_wait_for_owner'),
                    //     "buttons" => [
                    //         [
                    //             "title"  => __('mobile.ok'),
                    //             "action" => NextPage::POLICIES,
                    //             "type"   => "page",
                    //         ],
                    //     ]
                    // ];
        
                    // return Helpers::response('success', Enum::PAGE_ACTION_TYPE_MODAL, $modal);
                    
                } else {
                    //do the purchase
                }
            }

        }

        $decreaseMedical = false;
        $increaseDisability = false;
        $increaseCriticalillness = false;
        $increaseAccident = false;
        $increaseDeath = false;
        $actions = [];
		$coverageIds = [];
        $arrPlanChangeCoverages    =   [];

		foreach ($out['coverages'] as $coverage){
        	if($coverage->product_name == Enum::PRODUCT_NAME_MEDICAL && $coverage->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID){
        		$decreaseMedical = true;

				$old_coverage = Coverage::where('owner_id', $coverage->owner_id)
										->where('product_id', $coverage->product_id)
										->where('covered_id', $coverage->covered_id)
										->where(function ($q) {
											$q->where('status', Enum::COVERAGE_STATUS_ACTIVE)
											  ->orWhere('status', Enum::COVERAGE_STATUS_FULFILLED);
										})
										->orderBy('first_payment_on', 'desc')->first();

				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_medical'] = $coverage->deductible;

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_medical']      = $old_coverage->deductible;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}

				array_push($coverageIds, $coverage->id);
			}
            elseif($coverage->product_name == Enum::PRODUCT_NAME_DISABILITY && $coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
        		$increaseDisability = true;

				$old_coverage = Coverage::where('owner_id', $coverage->owner_id)
										->where('product_id', $coverage->product_id)
										->where('covered_id', $coverage->covered_id)
										->where(function ($q) {
											$q->where('status', Enum::COVERAGE_STATUS_ACTIVE)
											  ->orWhere('status', Enum::COVERAGE_STATUS_FULFILLED);
										})
										->orderBy('first_payment_on', 'desc')->first();

				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_disability'] = $coverage->coverage;

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_disability']      = $old_coverage->coverage;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}

				array_push($coverageIds, $coverage->id);
			}
            elseif($coverage->product_name == Enum::PRODUCT_NAME_CRITICAL_ILLNESS  && $coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
        		$increaseCriticalillness = true;

				$old_coverage = Coverage::where('owner_id', $coverage->owner_id)
										->where('product_id', $coverage->product_id)
										->where('covered_id', $coverage->covered_id)
										->where(function ($q) {
											$q->where('status', Enum::COVERAGE_STATUS_ACTIVE)
											  ->orWhere('status', Enum::COVERAGE_STATUS_FULFILLED);
										})
										->orderBy('first_payment_on', 'desc')->first();

				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_criticalillness'] = $coverage->coverage;

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_criticalillness']      = $old_coverage->coverage;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}

				array_push($coverageIds, $coverage->id);
			}
            elseif($coverage->product_name == Enum::PRODUCT_NAME_ACCIDENT  && $coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
        		$increaseAccident = true;

				$old_coverage = Coverage::where('owner_id', $coverage->owner_id)
										->where('product_id', $coverage->product_id)
										->where('covered_id', $coverage->covered_id)
										->where(function ($q) {
											$q->where('status', Enum::COVERAGE_STATUS_ACTIVE)
											  ->orWhere('status', Enum::COVERAGE_STATUS_FULFILLED);
										})
										->orderBy('first_payment_on', 'desc')->first();

				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_accident'] = $coverage->coverage;

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_accident']      = $old_coverage->coverage;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}

				array_push($coverageIds, $coverage->id);
			}
            elseif($coverage->product_name == Enum::PRODUCT_NAME_DEATH  && $coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
        		$increaseDeath = true;

				$old_coverage = Coverage::where('owner_id', $coverage->owner_id)
										->where('product_id', $coverage->product_id)
										->where('covered_id', $coverage->covered_id)
										->where(function ($q) {
											$q->where('status', Enum::COVERAGE_STATUS_ACTIVE)
											  ->orWhere('status', Enum::COVERAGE_STATUS_FULFILLED);
										})
										->orderBy('first_payment_on', 'desc')->first();

				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_death'] = $coverage->coverage;

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_death']      = $old_coverage->coverage;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}

				array_push($coverageIds, $coverage->id);
			}
		}

        $out['coverages'] = $this->detectDecreasedAndCreateOrder($out,$user);

        if (count($out['coverages']) == 0){
            if($decreaseMedical){
                $this->planChangeAction($user,$actions,$coverageIds);
            }

			$modal = [
                "body" => __('web/messages.total_amount_zero'),
                "buttons" => [
                    [
                        "title" => __('web/menu.policy'),
                        "action" => NextPage::POLICIES,
                        "type" => "page",
                    ],
                    [
                        "title" => __('web/menu.dashboard'),
                        "action" => NextPage::DASHBOARD,
                        "type" => "page",
                    ]
                ]
            ];
            return Helpers::response('success', Enum::PAGE_ACTION_TYPE_MODAL, $modal);
        }
         if (count($out['coverages']) == 0){
         if($increaseDisability){
                $this->planChangeAction($user,$actions,$coverageIds);    
            }
            elseif($increaseCriticalillness){
                $this->planChangeAction($user,$actions,$coverageIds);    
            }
            elseif($increaseAccident){
                $this->planChangeAction($user,$actions,$coverageIds);    
            }
            elseif($increaseDeath){
                $this->planChangeAction($user,$actions,$coverageIds);    
            }
            
          

			$modal = [
                "body" => __('web/messages.total_amount_zero'),
                "buttons" => [
                    [
                        "title" => __('web/menu.policy'),
                        "action" => NextPage::POLICIES,
                        "type" => "page",
                    ],
                    [
                        "title" => __('web/menu.dashboard'),
                        "action" => NextPage::DASHBOARD,
                        "type" => "page",
                    ]
                ]
            ];
            return Helpers::response('success', Enum::PAGE_ACTION_TYPE_MODAL, $modal);
        }
        

        $order = new Order();
        $order->amount = $out['total'] == 0 ? 0 : ($out['total'] + $out['transaction_fee']);
        $order->true_amount = $out['total'] == 0 ? 0 : ($out['total'] + $out['transaction_fee']);
        $order->status = Enum::ORDER_PENDING;
        $order->due_date = now();
        if(!empty($request->input('covered_payer_id'))) {        
            $covered_payer_id   =   (int)$request->input('covered_payer_id');
            $order->payer_id = $covered_payer_id;
        }
        else {
            $order->payer_id = $user->id;
        }
        
        $order->retries = 1;
        $order->type = Enum::ORDER_TYPE_NEW;
        $order->grace_period = 30;
        $order->last_try_on = now();
        $order->next_try_on = Carbon::today()->addDays(7);

        $order->save();

        $actions = [];
        $coverageIds = [];
        $newMember = FALSE;
        $arrPlanChangeCoverages =   [];

        foreach ($out['coverages'] ?? [] as $coverage) {
            $coverage = Coverage::whereUuid($coverage->uuid)->first();
            if (empty($coverage))
                continue;

            $old_coverage = Coverage::where('owner_id', $coverage->owner_id)
                ->where('product_id', $coverage->product_id)
                ->where('covered_id', $coverage->covered_id)
                ->where(function ($q) {
                    $q->where('status', Enum::COVERAGE_STATUS_ACTIVE)
                        ->orWhere('status', Enum::COVERAGE_STATUS_FULFILLED);
                })
                ->orderBy('first_payment_on', 'desc')->first();

            $first_payment_on = $old_coverage->first_payment_on ?? NULL;

			// add action for Member Addition
			if($coverage->product->name == 'Medical'){
				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_medical'] = $coverage->deductible;

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_medical']      = $old_coverage->deductible;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}
			}
             elseif($coverage->product->name == 'Disability'){
				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_disability'] = $coverage->coverage;
               

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_disability']      = $old_coverage->coverage;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}
			}
            elseif($coverage->product->name == 'Critical Illness'){
				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_criticalillness'] = $coverage->coverage;
               

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_criticalillness']      = $old_coverage->coverage;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}
			}
            elseif($coverage->product->name == 'Accident'){
				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_accident'] = $coverage->coverage;
               

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_accident']      = $old_coverage->coverage;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}
			}
            elseif($coverage->product->name == 'Death'){
				$actions['product_name'] = $coverage->product->name;
				$actions['new_payment_term'] = $coverage->payment_term;
				$actions['new_death'] = $coverage->coverage;
               

				if(!empty($old_coverage)){
					$actions['old_payment_term'] = $old_coverage->payment_term;
					$actions['old_death']      = $old_coverage->coverage;
					$actions['changed_at']       = Carbon::now();
					$actions['first_payment_on'] = $old_coverage->first_payment_on;
					$actions['current_annually'] = $old_coverage->payment_annually;

                    $arrPlanChangeCoverages[]   =   $coverage->id;
				}
			}

            array_push($coverageIds, $coverage->id);

            $newMemberCount = $user->actions()->where('event', Enum::ACTION_EVENT_NEW_MEMBER)->count();
            $terminateCount = $user->actions()->where('event', Enum::ACTION_EVENT_TERMINATE)->count();

			if (empty($first_payment_on) || ($terminateCount >= $newMemberCount)) {
				$newMember = true;
			}

            $coverage->first_payment_on = $first_payment_on;
            $coverage->next_payment_on = NULL;
            $coverage->last_payment_on = NULL;
            $coverage->uw_id = Underwriting::where("individual_id", $coverage->owner_id)->latest()->first()->id ?? NULL;
            $coverage->save();

            if (($coverage->payer->profile->id ?? 0) != $coverage->owner_id) {
                $owner = Individual::find($coverage->owner_id);
                $owner = $owner->user ?? NULL;
                $payer = User::find($coverage->payer_id);
                if (!empty($owner))
                    $owner->sendNotification('mobile.coverage_purchased_successfully_title','mobile.coverage_purchased_successfully_body',[
                    'translate_data' => ['payer_name' =>$payer->name ?? 'Somebody'],
                    'command' => 'next_page', 
                    'data' => 'policies_page']
                    );

            }

            $c_o = new CoverageOrder();
            $c_o->coverage_id = $coverage->id;
            $c_o->order_id = $order->id;
            $c_o->save();
        }


        $ownerId = $order->coverages()->first()->owner_id;
        $owner_user_id = Individual::where('id', $ownerId)->first()->user_id;
        if ($out['discount'] > 0) {
            Credit::createDepositSelf($owner_user_id, $order, $out['disc']);
        }

        try {
		    $payment = ProcessPayment::dispatchNow($order->id);
        } catch (\Throwable $e) {
            $modal = [
				"body"    => __('mobile.unsuccess_payment'),
				"buttons" => [
					[
						"title"  => __('web/menu.dashboard'),
						"action" => NextPage::DASHBOARD,
						"type"   => "page",
					],
				]
			];

            return Helpers::response('success', Enum::PAGE_ACTION_TYPE_MODAL, $modal);
        }

		if(empty($payment) || $payment->status != Enum::ORDER_SUCCESSFUL){

			$this->terminateAction($user,$actions,$coverageIds);

			$modal = [
				"body"    => __('mobile.unsuccess_payment'),
				"buttons" => [
					[
						"title"  => __('web/menu.dashboard'),
						"action" => NextPage::DASHBOARD,
						"type"   => "page",
					],
				]
			];

            return Helpers::response('success', Enum::PAGE_ACTION_TYPE_MODAL, $modal);
        }

		// add action
		if(!empty($actions)){
			if($newMember){
				$this->memberAdditionAction($user,$actions,$coverageIds,$arrPlanChangeCoverages);
			}else{
				$this->planChangeAction($user,$actions,$coverageIds);
			}
		}

        if ($request->input('fill_type') == 'pay_for_others') {
            $old = $user->profile->coverages_payer()->where("covered_id", $coverage->owner_id ?? 0)->where(function ($q){
                $q->where('status',Enum::COVERAGE_STATUS_ACTIVE)->orWhere('status',Enum::COVERAGE_STATUS_ACTIVE_INCREASED);
            });
            $old_sum = $old->sum('coverage');
        } elseif(!empty($request->input('covered_payer_id'))) {
            $old = $user->profile->coverages_owner()->where(function ($q){
                $q->where('status',Enum::COVERAGE_STATUS_ACTIVE)->orWhere('status',Enum::COVERAGE_STATUS_ACTIVE_INCREASED);
            });
            $old_sum = $old->sum('coverage');
        } else {
            $old = $user->profile->coverages_owner()->where("payer_id", $user->id ?? 0)->where(function ($q){
                $q->where('status',Enum::COVERAGE_STATUS_ACTIVE)->orWhere('status',Enum::COVERAGE_STATUS_ACTIVE_INCREASED);
            });
            $old_sum = $old->sum('coverage');

        }

        $old_status = Enum::COVERAGE_STATUS_CANCELLED;
        $new_sum = $order->coverages()->sum('coverage');
        if ($new_sum < $old_sum)
            $old_status = Enum::COVERAGE_STATUS_DECREASED;

        //        $old->update(['status'=> $old_status]);

        $old = $old->orderBy('created_at','desc');

        foreach ($order->coverages as $oneCoverage){

            if ($request->input('fill_type') == 'pay_for_others')
                $user->profile->coverages_payer()->where('product_name',$oneCoverage->product_name)->where("covered_id", $coverage->owner_id ?? 0)->whereStatus(Enum::COVERAGE_STATUS_DECREASE_UNPAID)->update(['status' => Enum::COVERAGE_STATUS_DECREASE_TERMINATE]);
            else
                $user->profile->coverages_owner()->where('product_name',$oneCoverage->product_name)->where("payer_id", $user->id ?? 0)->whereStatus(Enum::COVERAGE_STATUS_DECREASE_UNPAID)->update(['status' => Enum::COVERAGE_STATUS_DECREASE_TERMINATE]);

           // $oldClone = clone $old;
         //   $tmpSumOlds = $oldClone->where('product_id',$oneCoverage->product_id)->where('id','<>',$oneCoverage->id)->sum('coverage');
        //    if($tmpSumOlds!=0){
         //       $latestOld = $oldClone->latest()->first();
          //      $firstOld = $oldClone->first();
           //     $oneCoverage->status = Enum::COVERAGE_STATUS_ACTIVE_INCREASED;
           //     $oneCoverage->state = Enum::COVERAGE_STATE_ACTIVE;
           //     $oneCoverage->parent_id = $latestOld->id;
           //     $oneCoverage->next_payment_on = $firstOld->next_payment_on;
            //    $oneCoverage->save();
          //  }
           
           $oldClone = clone $old;
            $tmpSumOlds = $oldClone->where('product_id',$oneCoverage->product_id)->where('id','<>',$oneCoverage->id)->sum('coverage');
            if($tmpSumOlds!=0){
                $first_pay = Coverage::where('product_id',$oneCoverage->product_id)->where('owner_id',$oneCoverage->owner_id)->where('status','active')->first()->first_payment_on;
                $now = now();
                $diff_day = date_diff(date_create($now), date_create($first_pay));
                $diff = $diff_day->format("%y");
                $diff_month = $diff_day->format("%m") + 1;
                $latestOld = $oldClone->latest()->first();
                $firstOld = $oldClone->first();
                $oneCoverage->status = Enum::COVERAGE_STATUS_ACTIVE_INCREASED;
                $oneCoverage->state = Enum::COVERAGE_STATE_ACTIVE;
                $oneCoverage->parent_id = $latestOld->id;
                if($request->input('fill_type') == 'pay_for_others'){
                    if($diff_day->format("%y") < 1){
                    if($oneCoverage->payment_term == 'monthly'){
                $month_next = $first_pay->addMonth($diff_month);
                $oneCoverage->next_payment_on =  $month_next;
                    }else{
                $oneCoverage->next_payment_on = $first_pay->addYear();
                    }
                }else{
                    if($oneCoverage->payment_term == 'monthly'){
                        $month_next = $first_pay->addYear($diff);
                        $month_next = $month_next->addMonth($diff_month);
                        $oneCoverage->next_payment_on =  $month_next;
                            }else{
                                $diff = $diff +1;   
                        $oneCoverage->next_payment_on = $first_pay->addYear($diff);
                            }
                }
            }
                $oneCoverage->save();
            }
        }
        //->update(['status' => 'Active']);

        if(($coverage->payer->profile->id ?? 0) != $coverage->owner_id)
        {

            $locale = $coverage->payer->locale;
            App::setLocale($locale ?? 'en');
            $emailText = __('mobile.payer_owner_completed_purchase', [
				'payer_name' => $coverage->payer->profile->name,
				'owner_name' => $coverage->owner->user->profile->name,
                'him_her'	 => ($coverage->owner->user->profile->gender == 'female') ? __('mobile.her') : __('mobile.him') ?? __('mobile.him'),

			]);
			$coverage->payer->sendNotification(__('mobile.payer_owner_completed_purchase_title'),strip_tags($emailText),['command' => 'next_page','data' => 'policies_page']);
            $coverage->payer->notify(new \App\Notifications\Email($emailText, ['subject' => __('mobile.payer_owner_completed_purchase_title')]));
        }

        $locale = $coverage->owner->user->locale;
        App::setLocale($locale ?? 'en');

        $links = [];
        $coveragesName = [];
        $notif_user = $coverage->owner->user;
        GenerateDocument::dispatch($out['coverages'],$notif_user->id,__('mobile.payment_success_desc', ['name' => ucwords(strtolower($user->name))]),__('mobile.payment_success_subject'));
        
//        foreach ($out['coverages'] ?? [] as $coverage) {
//            $coverage = Coverage::whereUuid($coverage->uuid)->first();
//            if (empty($coverage))
//                continue;
//
//            $req = Request::create('/doc', 'GET', ['app_view' => Helpers::isFromApp() ? '1' : '2', 'coverage' => $coverage->uuid ?? '-1', 'type' => 'contract', 'uuid' => encrypt($coverage->covered->user_id),'encryption'=>Carbon::parse($user->profile->dob)->format('dMY'),'need_save'=>true]);
//            app()->handle($req);
//
//            $links[] = [
//                'file'=>Storage::disk('s3')->get($coverage->documents()->latest()->first()->path),
//                'name'=>$coverage->product_name.'.'.$coverage->documents()->latest()->first()->ext
//            ];
//
//        }
//        $notif_user = $coverage->owner->user;
//        if (!empty($notif_user)) {
//            $notif_user->notify(new Email(__('mobile.payment_success_desc', ['name' => $notif_user->name]), [
//                'confetti' => TRUE,
//                'attachments'=>$links
//            ]));
//        }

        return ['status' => 'success', 'data' => ['order_confirmation' => $order->transactions()->where('success', 1)->first()->transaction_id, 'next_page' => 'order_receipt_page']];

    }

    /**
     * @api {get} api/orderReview order review
     * @apiVersion 1.0.0
     * @apiName OrderReview
     * @apiGroup Policy
     *
     * @apiDescription It shows user's payment pending coverages for complete process payment. notice,if one of nominees is same with payer and they dont have any near relationship, system show alert and user cant conduct payment.
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} fill_type empty or pay_for_others
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {Array} data[beneficiaries]
     * @apiSuccess (Response (200) ) {Array} data[thanksgiving]
     * @apiSuccess (Response (200) ) {Array} data[coverages]
     * @apiSuccess (Response (200) ) {Number} data[total]
     * @apiSuccess (Response (200) ) {Number} data[transaction_fee]
     * @apiSuccess (Response (200) ) {String} data[declaration_link]
     * @apiSuccess (Response (200) ) {String} data[pdpa_link]
     * @apiSuccess (Response (200) ) {Number} data[discount]
     *
     * @apiError  {String} status error
     * @apiError  {String} message
     *
     *
     */
    public function orderReview(Request $request)
    {
        $user = \auth('web')->user();
        if (empty($user))
            $user = auth()->user();

        $user = $user->profile;
        $user_c = $user;

       //added for remove card in corporate flow
        $check_coverage_offered = $user->coverages_owner()->whereIn('status',['unpaid','increase-unpaid','decrease-unpaid'])->where('state','inactive')->latest()->first()->payer_id ?? null;
        $check_coverage_by_corp = User::where('id',$check_coverage_offered)->first()->corporate_type ?? null;
        $check_user_id = $user->id;
        if($check_coverage_offered != null){
            $corp_individual_check = ($check_coverage_offered != $user->id) && $check_coverage_by_corp=='payorcorporate';
        }else { $corp_individual_check = false; }

        //dev-504 - Confirm Your Order page - missing & incorrect display for assignee 'DearTime - Charity Fund'
        $beneficiaries = $user->beneficiaries()->select(['name', 'name AS tr_name', 'percentage', 'relationship', 'nominee_id'])->get(); // for charity how
        $thanksgiving = $user->thanksgiving;
        $payment = $user->bankCards()->select(['masked_pan'])->first()->cc ?? '';

        // for policies must get the new price if they are changed

        $next_page_data     =   [];
        if(!empty($request->input('fill_type')) && !empty($request->input('user_id')))
            $next_page_data     =   [
                'fill_type' => $request->input('fill_type'),
                'user_id'   => $request->input('user_id')
            ];

        if ($user->thanksgiving()->count() == 0)
            return ['status' => 'success', 'data' => ['next_page' => 'thanksgiving_page', 'next_url' => route('userpanel.Thanksgiving.index'), 'next_page_data' => $next_page_data, 'msg' => __('web/policy.first_fill_thanksgiving')]];

        if ($user->bankCards()->count() == 0 && !$user->is_charity() && (!$corp_individual_check))
            return ['status' => 'success', 'data' => ['next_page' => 'payment_details_page', 'next_url' => route('userpanel.bank_card.index'), 'next_page_data' => [], 'msg' => __('web/policy.first_fill_bank_card')]];

        //        if(empty($user->fund_source))
        //            return ['status' => 'success','data'=>['next_page'=>'payment_details_page','next_url'=>route('userpanel.bank_card.index'),'next_page_data'=>[],'msg'=>__('web/policy.first_fill_fund_source')]];


        if ($request->input('fill_type') == 'pay_for_others') {
            $user = Individual::WithChild()->whereUuid($request->input('user_id'))->first();
            $coverages = $user_c->coverages_payer()->where("covered_id", $user->id ?? 0);

            $beneficiaries = [];
            $thanksgiving = [];
        } else {
            if(!empty($request->input('covered_payer_id'))) {
                $coverages = $user->coverages_owner()->where('payer_id', '=', $request->input('covered_payer_id'));
            }
            else {
                $coverages = $user->coverages_owner()->where('payer_id', '=', $user->user_id);
            }

            /*if ($user->beneficiaries()->count() == 0)
                return ['status' => 'success', 'data' => ['next_page' => 'nominee_page', 'next_url' => route('userpanel.Beneficiary.index'), 'next_page_data' => [], 'msg' => __('web/policy.first_fill_nominee')]];*/

            if (!$user->isVerified())
                return ['status' => 'success', 'data' => ['next_page' => 'verification_page', 'next_url' => route('userpanel.Verification.index'), 'next_page_data' => $next_page_data, 'msg' => __('web/policy.first_fill_verification')]];

        }


        $deathCoverage = clone $coverages;
        $deathCoverage = $deathCoverage->where("product_name", Enum::PRODUCT_NAME_DEATH)->sum('coverage') ?? NULL;
        $coverages = $coverages->select(['id','payer_id','state','status','covered_id', 'uuid', 'owner_id', 'product_name', 'product_id', 'coverage', 'payment_term', 'payment_monthly', 'payment_annually', 'deductible'])->where(function ($q) {
            $q->where('status', Enum::COVERAGE_STATUS_UNPAID)
                ->orWhere('status',Enum::COVERAGE_STATUS_INCREASE_UNPAID)
                ->orWhere('status',Enum::COVERAGE_STATUS_DECREASE_UNPAID);
        })->get();

        //        $coverages = $user->coverages_owner()->get();

         if ($request->input('fill_type') == 'pay_for_others') {
            foreach ($coverages as $coverage) {
                if($coverage->product_name == 'Medical'){
                    $coverage->coverage = $coverage->deductible;
                }else{
                    $coverage->coverage = $coverage->coverage;
                }
                //  $coverage->product_name .= ' ( ' . $user->name . ' )';
                // $coverage->payer = $user->name;
            }
        }

        // check current coverage with active coverage for increase or decrease
        $total = 0;
        $discount = 0;
        // print_r($coverages);
        // exit;
        foreach ($coverages as $coverage) {
            if (!isset($coverage->existing_coverage)) 
                $coverage->existing_coverage = 0;
            if (!isset($coverage->forecast_total_cover)) 
                $coverage->forecast_total_cover = 0;
            if (!isset($coverage->cov_operation)) 
                $coverage->cov_operation = "";
            
            //Todo Imp -> Do we need to consider FullFilled state for existing coverage ? Not required right ? 
            if($user->id != $user_c->id) {
                $active_coverage = Coverage::where("covered_id", $user->id ?? 0)
                ->where('payer_id',$user_c->user_id)
                ->where("product_id", $coverage->product_id)
                ->where("state", Enum::COVERAGE_STATE_ACTIVE)
                // Sorting is Only required for Mecial Product to get the last deductible 
                // which will be shown as Cover in Mobile view
                ->orderby('id', 'desc');
            }
            else {
                $active_coverage = $user_c->coverages_owner()
                ->where("covered_id", $user->id ?? 0)
                ->where("product_id", $coverage->product_id)
                ->where("state", Enum::COVERAGE_STATE_ACTIVE)
                // Sorting is Only required for Mecial Product to get the last deductible 
                // which will be shown as Cover in Mobile view
                ->orderby('id', 'desc');
            }
             
            if($user->id == $user_c->id) {               
                //To compute the existing coverage details and the new coverage request details
                if ($coverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                    $medical_prod_active_coverage = $active_coverage->first();
                    $total_cov = $medical_prod_active_coverage != null ? 
                            $medical_prod_active_coverage->deductible : 0; 
                    $coverage->existing_coverage = $total_cov;
                    if($coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
                        //To compute the new coverage req details  (OVERALL COVERAGE DETAILS)
                        $coverage->forecast_total_cover = $total_cov + $coverage->deductible;
                    } else {
                        //To compute the new coverage req details  (OVERALL COVERAGE DETAILS)
                        $coverage->forecast_total_cover = $coverage->deductible;
                    }
                } else {
                    $coverage->existing_coverage += $active_coverage->sum('coverage');
                    //To compute the new coverage req details
                    // $coverage->forecast_total_cover += $coverage->coverage;
                    if($coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
                        //To compute the new coverage req details  (OVERALL COVERAGE DETAILS)
                        $coverage->forecast_total_cover = $coverage->existing_coverage + $coverage->coverage;
                    } else {
                        //To compute the new coverage req details  (OVERALL COVERAGE DETAILS)
                        $cov_active = $user_c->coverages_owner()
                        ->where("covered_id", $user->id ?? 0)
                        ->where("product_id", $coverage->product_id)
                        ->where("state", Enum::COVERAGE_STATE_ACTIVE)
                        // Sorting is Only required for Mecial Product to get the last deductible 
                        // which will be shown as Cover in Mobile view
                        ->orderby('id', 'desc')->get();

                        if($cov_active->isNotEmpty()){
                            if($cov_active->sum('coverage') != 0 && $coverage->status == Enum::COVERAGE_STATUS_UNPAID){
                            $coverage->forecast_total_cover = $coverage->existing_coverage + $coverage->coverage;
                            }else{
                            $coverage->forecast_total_cover = $coverage->coverage;
                            }
                        }else{
                            $coverage->forecast_total_cover = $coverage->coverage;

                        }
                    }
                }

                
                if($coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
                    $coverage->cov_operation = "+";
                    // $coverage->forecast_total_cover = $coverage->existing_coverage + $coverage->coverage;
                }
                
                  // 1-Mar-24 (During Ind payor we noticed in confirm order page the forecast amount for decraese unpaid is not correct)

                if($coverage->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID){
                    $coverage->cov_operation = "-";
                    $payer_cov =Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('payer_id','<>',$coverage->payer_id)->get();
                    if($payer_cov->isNotEmpty()){
                        $cov_sum =Coverage::where('owner_id',$coverage->owner_id)->where('product_id',$coverage->product_id)->where('payer_id','<>',$coverage->payer_id)->where('state','active')->sum('coverage');
                       $coverage->forecast_total_cover = $cov_sum + $coverage->coverage;
                    }
                    // $coverage->forecast_total_cover = $coverage->existing_coverage + $coverage->coverage;
                }

                //Cancellation
                if($coverage->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID && $coverage->coverage == 0){
                    $coverage->cov_operation = "cancel";
                    $coverage->forecast_total_cover = 0;
                }
                
                // check increased
                if ($coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID) {
                    $coverage->cov_operation = "+";
                    $this->beneficiaryAssignment($coverage->owner_id);

                    $diff = $coverage->product_name == Enum::PRODUCT_NAME_MEDICAL?$coverage->real_coverage: $coverage->coverage;
                    $prod_options = $coverage->product->quickQuoteFor($user, $diff, $deathCoverage,$user_c,$coverage->product_name == Enum::PRODUCT_NAME_MEDICAL ? $diff : null);
                    $now = now();
                    $days = $now->startOfDay()->diffInDays($active_coverage->first()->next_payment_on ?? $now);

                    // if (!$now->gt($active_coverage->first()->next_payment_on ?? $now)) {
                    //     if($coverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                    //         $coverage->payment_monthly = Helpers::proRate($prod_options['monthly'] - $active_coverage->sum('payment_monthly'), Carbon::now()->daysInMonth, $days);
                    //         $coverage->payment_annually = Helpers::proRate($prod_options['annually'] - $active_coverage->sum('payment_annually'), Carbon::now()->daysInYear, $days);
                    //     }
                    //     else{
                    //         $coverage->payment_monthly = Helpers::proRate($prod_options['monthly'], Carbon::now()->daysInMonth, $days);
                    //         $coverage->payment_annually = Helpers::proRate($prod_options['annually'], Carbon::now()->daysInYear, $days);
                    //     }
                    // };
                    $coverage->diff = $diff;

                } else{
                    $diff = $coverage->product_name == Enum::PRODUCT_NAME_MEDICAL?$coverage->real_coverage: $coverage->coverage;
                    if(!empty($active_coverage)){
                        if($active_coverage->sum('coverage') != 0){
                            $coverage->diff = $diff;
                        }
                    }else{
                         $coverage->diff = 0;

                    }
                }
                

                if ($coverage->coverage != 0) {
                    if($coverage->status != Enum::COVERAGE_STATUS_DECREASE_UNPAID){
                        $total += ($coverage->payment_term == 'annually') ? ($coverage->payment_annually < 0 ? 0 : $coverage->payment_annually) : ($coverage->payment_monthly < 0 ? 0 : $coverage->payment_monthly);
                        $ownerThanksgiving = Thanksgiving::where('individual_id', $coverage->owner_id)->get();
                        $cov_term = ($coverage->payment_term == 'annually') ? ($coverage->payment_annually < 0 ? 0 : $coverage->payment_annually) : ($coverage->payment_monthly < 0 ? 0 : $coverage->payment_monthly);
                        $discount += Helpers::calcThanksgivingDiscount($ownerThanksgiving, $cov_term);
                    }
                } else {
                    $coverage->payment_monthly = 0;
                    $coverage->payment_annually = 0;
                }
            }
            else {
                if ($coverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                    $medical_prod_active_coverage = $active_coverage->first();
                    $total_cov = $medical_prod_active_coverage != null ? 
                            $medical_prod_active_coverage->deductible : 0; 
                    $coverage->existing_coverage = $total_cov;
                    if($coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
                        //To compute the new coverage req details  (OVERALL COVERAGE DETAILS)
                        $coverage->forecast_total_cover = $total_cov + $coverage->deductible;
                    } else {
                        //To compute the new coverage req details  (OVERALL COVERAGE DETAILS)
                        $coverage->forecast_total_cover = $coverage->deductible;
                    }
                } else {
                    $coverage->existing_coverage += $active_coverage->sum('coverage');
                    //To compute the new coverage req details
                    // $coverage->forecast_total_cover += $coverage->coverage;
                    if($coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
                        //To compute the new coverage req details  (OVERALL COVERAGE DETAILS)
                        $coverage->forecast_total_cover = $coverage->existing_coverage + $coverage->coverage;
                     }elseif($coverage->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID){
                        //To compute the new coverage req details  (OVERALL COVERAGE DETAILS)
                        $coverage->forecast_total_cover = $coverage->coverage;
                    }else{
                        $coverage->forecast_total_cover = $coverage->coverage;
                    }
                }

                
                if($coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID){
                    $coverage->cov_operation = "+";
                    // $coverage->forecast_total_cover = $coverage->existing_coverage + $coverage->coverage;
                }
                
                if($coverage->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID){
                    $coverage->cov_operation = "-";
                    // $coverage->forecast_total_cover = $coverage->existing_coverage + $coverage->coverage;
                }

                //Cancellation
                if($coverage->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID && $coverage->coverage == 0){
                    $coverage->cov_operation = "cancel";
                    $coverage->forecast_total_cover = 0;
                }
                
                // check increased
                if ($coverage->status == Enum::COVERAGE_STATUS_INCREASE_UNPAID) {
                    $coverage->cov_operation = "+";
                    $this->beneficiaryAssignment($coverage->owner_id);

                    $diff = $coverage->product_name == Enum::PRODUCT_NAME_MEDICAL?$coverage->real_coverage: $coverage->coverage;
                    $prod_options = $coverage->product->quickQuoteFor($user, $diff, $deathCoverage,$user_c,$coverage->product_name == Enum::PRODUCT_NAME_MEDICAL ? $diff : null);
                    $now = now();
                    $days = $now->startOfDay()->diffInDays($active_coverage->first()->next_payment_on ?? $now);
                    //Commented the below lines for payor side order review payment monthly and payment annually 0 (Suraya issue) 
                    // if (!$now->gt($active_coverage->first()->next_payment_on ?? $now)) {
                    //     if($coverage->product_name == Enum::PRODUCT_NAME_MEDICAL){
                    //         $coverage->payment_monthly = Helpers::proRate($prod_options['monthly'] - $active_coverage->sum('payment_monthly'), Carbon::now()->daysInMonth, $days);
                    //         $coverage->payment_annually = Helpers::proRate($prod_options['annually'] - $active_coverage->sum('payment_annually'), Carbon::now()->daysInYear, $days);
                    //     }
                    //     else{
                    //         $coverage->payment_monthly = Helpers::proRate($prod_options['monthly'], Carbon::now()->daysInMonth, $days);
                    //         $coverage->payment_annually = Helpers::proRate($prod_options['annually'], Carbon::now()->daysInYear, $days);
                    //     }
                    // };
                    $coverage->diff = $diff;

                } else{
                    $coverage->diff = 0;
                }
                

                if ($coverage->coverage != 0) {
                    if($coverage->status != Enum::COVERAGE_STATUS_DECREASE_UNPAID){
                        $total += ($coverage->payment_term == 'annually') ? ($coverage->payment_annually < 0 ? 0 : $coverage->payment_annually) : ($coverage->payment_monthly < 0 ? 0 : $coverage->payment_monthly);
                       $ownerThanksgiving = Thanksgiving::where('individual_id', $coverage->owner_id)->get();
                        $cov_term = ($coverage->payment_term == 'annually') ? ($coverage->payment_annually < 0 ? 0 : $coverage->payment_annually) : ($coverage->payment_monthly < 0 ? 0 : $coverage->payment_monthly);
                        $discount += Helpers::calcThanksgivingDiscount($ownerThanksgiving, $cov_term);
                    }
                } else {
                    $coverage->payment_monthly = 0;
                    $coverage->payment_annually = 0;
                }
            }

        }

        foreach ($beneficiaries as $bn) {
            foreach ($coverages as $cg) {
                if ($bn->nominee_id == $cg->payer_id) {
                    $payer = $cg->payer;
                    if ($bn->relationship == 'other') {
                        $modal = [
                            "body" => __('mobile.other_beneficiary_not_payer', ['payer' => $payer->profile->name]),
                            "buttons" => [
                                [
                                    "title" => __('mobile.ok'),
                                    "action" => NextPage::POLICIES,
                                    "type" => "page",
                                ],
                            ]
                        ];
                        return Helpers::response('success', Enum::PAGE_ACTION_TYPE_MODAL, $modal);
                    }
                }
            }
        }

        $transaction_fee = $total * config('static.transaction_fee');
        //        if($transaction_fee < config('static.transaction_fee_min'))
        //            $transaction_fee = config('static.transaction_fee_min');

        if ($total == 0)
            $transaction_fee = 0;

        if ($total == 0 && $coverages->count() == 0)
            return ['status' => 'error', 'message' => __('web/messages.no_coverage')];

        $disc  = $total - $discount;
 
        // commented the below lines due to discount rounding issue
        // $ownerThanksgiving = Thanksgiving::where('individual_id', $coverages->first()->owner_id)->get();
        // $discount = Helpers::calcThanksgivingDiscount($ownerThanksgiving, $total);

        // print_r($coverages);
        // exit;

        return [
            'status' => 'success', 'data' =>
                [
                    'beneficiaries' => $beneficiaries,
                    'thanksgiving' => $thanksgiving,
                    'coverages' => $coverages,
                    'payment' => $payment,
                    'total' => $total,
                    'transaction_fee' => $transaction_fee,
                    'declaration_link' => route('page.index', ['page' => 'declaration']),
                    'pdpa_link' => route('page.index', ['page' => 'order_pdpa']),
                    'important_notice_link' => route('page.index', ['page' => 'importantNotice']),
                    'discount' => round($discount,2),
                    'disc'     => $disc,
                    'is_corporate_offer' => ($coverages->first()->payer->corporate_type == 'payorcorporate')
                ]
        ];

    }
    
    public function paymentHistory(Request $request)
    {
        //        $history = $request->user()->orders_payer()->select('id','uuid','amount','status','payer_id','created_at')->where("amount",">=","1")->orderBy("created_at","desc")->get();
        //        foreach ($history as $item) {
        //            $coverages = [];
        //            foreach ($item->coverages as $coverage) {
        //                $coverages[] = ['name'=>$coverage->product_name,'coverage'=>$coverage->coverage];
        //            }
        //            $item->coverages = $coverages;
        //        }

        $payer_id = $request->user()->id;
        $history = Transaction::whereHas('order', function ($query) use ($payer_id) {
            $query->where('payer_id', $payer_id);
        })->latest()->get();

        foreach ($history as $item) {
            $coverages = [];
            foreach ($item->order->coverages as $coverage) {
                $coverages[] = ['name' => $coverage->product_name, 'coverage' => $coverage->coverage];
            }
            $item->coverages = $coverages;
        }

        return ['status' => 'success', 'data' => $history];

    }

    public function corporateOrderReview(Request $request)
    {
        $pkg_id = $request->input('pkg_id');
        $company = $request->user()->profile;
        $pkg = $company->packages()->whereUuid($pkg_id)->first();
        if (empty($pkg))
            abort(404);

        $payment = $company->bankCards()->select(['cc_num'])->first()->cc ?? '';

        return [
            'status' => 'success', 'data' => [
                'package_details' => $pkg,
                'payment' => $payment,
                'members' => $pkg->members()->count()
            ]
        ];

    }

    private function detectDecreasedAndCreateOrder($out,$user)
    {
        foreach ($out['coverages'] as $key=>$coverage) {
            if ($coverage->status == Enum::COVERAGE_STATUS_DECREASE_UNPAID){
                $old_coverage = Coverage::where('owner_id', $coverage->owner_id)
                    ->where('product_id', $coverage->product_id)
                    ->where('covered_id', $coverage->covered_id)
                    ->where(function ($q) {
                        $q->Where('state', Enum::COVERAGE_STATE_ACTIVE);
                    })
                    ->orderBy('created_at', 'asc')->first();
                Coverage::whereId($coverage->id)->update(
                    [
                        'first_payment_on' => $old_coverage->first_payment_on,
                        'next_payment_on' => $old_coverage->next_payment_on,
                        'parent_id' => $old_coverage->id,
                        'renewal_date' =>$old_coverage->renewal_date
                    ]
                );

                $cov_order_check =CoverageOrder::where('coverage_id',$coverage->id)->latest()->first();
                if($cov_order_check){
                $order_check =Order::where('id', $cov_order_check->order_id)->where('status',Enum::ORDER_PENDING)->where('type',Enum::ORDER_TYPE_RENEW)->latest()->first();
                }else{
                    $order_check=[];
                }
 
                if(empty($order_check)){
                $order = new Order();
                $order->amount = ($coverage->payment_term == 'annually') ? $coverage->payment_annually : $coverage->payment_monthly + $out['transaction_fee'];
                $order->true_amount =  ($coverage->payment_term == 'annually') ? $coverage->payment_annually : $coverage->payment_monthly + $out['transaction_fee'];
                $order->status = Enum::ORDER_PENDING;
                $order->due_date = ($coverage->payment_term == 'annually')?Carbon::today()->addDays(Carbon::now()->daysInYear):Carbon::today()->addDays(30);
                $order->payer_id = $user->id;
                $order->retries = 5;
                $order->type = Enum::ORDER_TYPE_RENEW;
                $order->grace_period = 30;
                $order->last_try_on = now();
                //$order->next_try_on = ($coverage->payment_term == 'annually')?Carbon::today()->addDays(365):Carbon::today()->addDays(30);
                $order->save();


                $c_o = new CoverageOrder();
                $c_o->coverage_id = $coverage->id;
                $c_o->order_id = $order->id;
                $c_o->save();
                
                event(new ChangedCoveragesStatusEvent($order->coverages,$order,Enum::COVERAGE_STATUS_DECREASE_UNPAID));
                }
                unset($out['coverages'][$key]);
                
            }
        }

        return $out['coverages'];
    }

	/**
	 * @param $user
	 * @param array $actions
	 * @param array $coverageIds
	 */
	private function terminateAction($user,array $actions,array $coverageIds): void
	{
		$action = $user
			->actions()
			->create([
						 'user_id'    => $user->id,
						 'type'       => Enum::ACTION_TYPE_TERMINATE,
						 'event'      => Enum::ACTION_EVENT_TERMINATE,
						 'actions'    => $actions,
						 'execute_on' => Carbon::now(),
						 'status'     => Enum::ACTION_STATUS_EXECUTED
					 ]);

		$action->coverages()->attach($coverageIds);
	}

	/**
	 * @param $user
	 * @param array $actions
	 * @param array $coverageIds
	 */
	private function memberAdditionAction($user,array $actions,array $coverageIds, $arrPlanChangeCoverages = []): void
	{
		$action = $user
			->actions()
			->create([
						 'user_id'    => $user->id,
						 'type'       => Enum::ACTION_TYPE_MEMEBR_ADDITION,
						 'event'      => Enum::ACTION_EVENT_NEW_MEMBER,
						 'actions'    => $actions,
						 'execute_on' => Carbon::now(),
						 'status'     => Enum::ACTION_STATUS_EXECUTED,
                         'plan_change_coverage_ids' => implode(',', $arrPlanChangeCoverages)
					 ]);
		$action->coverages()->attach($coverageIds);
	}

	/**
	 * @param $user
	 * @param array $actions
	 * @param array $coverageIds
	 */
	private function planChangeAction($user,array $actions,array $coverageIds): void
	{
		$action = $user
			->actions()
			->create([
						 'user_id'    => $user->id,
						 'type'       => Enum::ACTION_TYPE_PLAN_CHANGE,
						 'event'      => Enum::ACTION_EVENT_PLAN_CHANGE,
						 'actions'    => $actions,
						 'execute_on' => Carbon::now(),
						 'status'     => Enum::ACTION_STATUS_EXECUTED
					 ]);
		$action->coverages()->attach($coverageIds);

		//$actions = collect($action->actions)->first();

		if($actions['old_payment_term'] == Enum::COVERAGE_PAYMENT_TERM_MONTHLY && $actions['new_payment_term'] == Enum::COVERAGE_PAYMENT_TERM_ANNUALLY){
			$totalCredit = 0;
			//foreach ($actions as $actionItem){
				$to = Carbon::parse($actions['first_payment_on']);
				$from = Carbon::parse($actions['changed_at']);
				$diffInMonths = ceil($to->floatDiffInMonths($from));
				$totalCredit += round(($actions['current_annually'] * (12 - $diffInMonths)) / 12,2);
			//}
			if($totalCredit > 0){
				Credit::create([
								   'from_id'      => $action->user_id,
								   'amount'       => -$totalCredit,
								   'type'         => Enum::CREDIT_TYPE_ACTION,
								   'type_item_id' => $action->id,
							   ]);
			}
		}
	}

	// set pending for charity then change status if it was success payment
	public function beneficiaryAssignment($ownerId){
        Beneficiary::where('individual_id', $ownerId)->where('type',Enum::BENEFICIARY_TYPE_HIBAH)
            ->where('percentage','>',0)->update([
                'status'=> 'registered'
            ]);
    }

    private function corpmax_coverage($product,$userage,$monthlyincome,$profile){
		
		if($product == 1){
            if($profile->occ == 983 || $profile->occ == 619 || $profile->occ == 620 || $profile->occ == 1069){
                $max = 350000;
                return $max;
            }else{
                return min (500000,max(min (25, 65 - $userage), 10) * 12 * $monthlyincome);

            }
		}elseif($product == 2){
            if($profile->occ == 983 || $profile->occ == 619 || $profile->occ == 620 || $profile->occ == 1069){
                $max = 250000;
                return $max;
            }else{
                return min (350000,max(min (25, 65 - $userage), 10) * 12 * $monthlyincome);

            }
		}elseif($product == 4){
            if($profile->occ == 983 || $profile->occ == 619 || $profile->occ == 620 || $profile->occ == 1069){
                $max = 250000;
                return $max;
            }else{
                return min (350000,max(min (25, 60 - $userage), 10) * 12 * $monthlyincome);

            }
		}
		}

    private function corporate_ageocccheck($profile,$product,$coverage){
        $occ =IndustryJob::where('id',$profile->occ)->first();
        if($product == 1){
            $occ_load =$occ->death;
            if($profile->age() > 65){
                $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                $coverage->save();
            }
            if($occ_load == -1){
                $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                $coverage->save();
            }


        }elseif($product == 2){
            $occ_load =$occ->TPD;
            if($profile->age() > 65){
                $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                $coverage->save();
            }
            if($occ_load == -1){
                $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                $coverage->save();
            }


        }elseif($product == 3){
            $occ_load =$occ->Accident;
            if($profile->age() > 65){
                $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                $coverage->save();
            }
            if($occ_load == -1){
                $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                $coverage->save();
            }

        }elseif($product == 4){
            if($profile->age() > 60){
                $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                $coverage->save();
            }
        }else{
            $occ_load =$occ->Medical;
            if($profile->age() > 55){
                $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                $coverage->save();
            }
            if($occ_load == -1){
                $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                $coverage->save();
            }

        }

    }
	
    public function corporate_coverageupdate(Request $request){
	
		$profile =$request->user()->profile;
        $si_applicant = false;
        if($profile->is_charity()){
            $si_applicant = true;
        }
        $payer_id =$request->get('payer_id');
		$userage =$profile->age();
		$income =$profile->personal_income;
        $corporate_type  =User::where('id',$payer_id)->first()->corporate_type;
        if( $corporate_type=='payorcorporate'){
            $corporate_payer =true;
        }else{
            $corporate_payer =false;
        }
	   
		$coverages_offered =$request->user()->profile->coverages_owner()->where('payer_id',$payer_id)->whereIn('status',['unpaid','increase-unpaid','decrease-unpaid'])->get();
        //$coverages_offered=$coverages_owned->where('payer_id','!=',$request->user()->profile->user_id)->get();
		if(($coverages_offered)->isNotEmpty()){
	
			foreach ($coverages_offered as $coverage){
				if($coverage->payer->corporate_type=='payorcorporate'){

                 $this->corporate_ageocccheck($profile,$coverage->product_id,$coverage);
                 if($coverage->product_id != 3 && $coverage->product_id != 5 ){
					$max_cov = $this->corpmax_coverage($coverage->product_id,$userage,$income,$profile);
                    $product_coverage= $request->user()->profile->coverages_owner()->where('state','active')->where('product_id',$coverage->product_id)->get();

                    
                    if($product_coverage->isNotEmpty()){
                       if($product_coverage->sum('coverage') >= $max_cov){
                        $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                        $coverage->save();
                       }else{
                        $corp_eligible = $max_cov - $product_coverage->sum('coverage');
                        if($corp_eligible <$coverage->coverage){
                            $coverage->coverage = ( $max_cov - $product_coverage->sum('coverage'));
                            $coverage->save();
                            
                        }
                        
                       }
                        
                    }else{
                    if($coverage->coverage >$max_cov){
					   $coverage->coverage = $max_cov;
					   $coverage->save();
                    }
                    }
                    
                    
					//Helpers::updatePremiumOnOccupation($profile);
                 }

                 if($coverage->product_id == 3){
                    $death_cov =$request->user()->profile->coverages_owner()->where('product_id',1)->where('status','!=','terminate')->get();
                    if(!$death_cov->isNotEmpty()){
                        $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                        $coverage->save();
                    }
                    $accmax_cov = $request->user()->profile->coverages_owner()->where('product_id',1)->where('status','!=','terminate')->sum('coverage');
                    $accident_coverage= $request->user()->profile->coverages_owner()->where('state','active')->where('product_id',$coverage->product_id)->get();
                    if($accident_coverage->isNotEmpty()){
                    if($accident_coverage->sum('coverage')>=$accmax_cov ){
                        $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                        $coverage->save();
                    }else{
                        $acc_corp_eligible = $accmax_cov - $accident_coverage->sum('coverage');
                        if($acc_corp_eligible < $coverage->coverage){
                            $coverage->coverage = ($accmax_cov -$accident_coverage->sum('coverage') );
                            $coverage->save();
                            
                        }
                       
                       }
                   }else{
                    if($coverage->coverage >$accmax_cov){
					   $coverage->coverage = $accmax_cov;
					   $coverage->save();
                    }
                    }

                 }

                
				}else{
                    $this->corporate_ageocccheck($profile,$coverage->product_id,$coverage);
                    
                    if($coverage->product_id != 3 && $coverage->product_id != 5 ){
                        $max_cov = $coverage->product->maxCoverage($profile);
                        // $max_cov = $this->corpmax_coverage($coverage->product_id,$userage,$income,$profile);
                        $product_coverage= $request->user()->profile->coverages_owner()->where('state','active')->where('product_id',$coverage->product_id)->get();
    
                        
                        if($product_coverage->isNotEmpty()){
                           if($product_coverage->sum('coverage') >= $max_cov){
                            $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                            $coverage->save();
                           }else{
                            $corp_eligible = $max_cov - $product_coverage->sum('coverage');
                            if($corp_eligible <$coverage->coverage){
                                $coverage->coverage = ( $max_cov - $product_coverage->sum('coverage'));
                                $coverage->save();
                                
                            }
                            
                           }
                            
                        }else{
                        if($coverage->coverage >$max_cov){
                           $coverage->coverage = $max_cov;
                           $coverage->save();
                        }
                        }
                        
                        
                        //Helpers::updatePremiumOnOccupation($profile);
                     }
    
                     if($coverage->product_id == 3){
                        $death_cov =$request->user()->profile->coverages_owner()->where('product_id',1)->where('status','!=','terminate')->get();
                        if(!$death_cov->isNotEmpty()){
                            $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                            $coverage->save();
                        }
                        $accmax_cov = $request->user()->profile->coverages_owner()->where('product_id',1)->where('status','!=','terminate')->sum('coverage');
                        $accident_coverage= $request->user()->profile->coverages_owner()->where('state','active')->where('product_id',$coverage->product_id)->get();
                        if($accident_coverage->isNotEmpty()){
                        if($accident_coverage->sum('coverage')>=$accmax_cov ){
                            $coverage->status =Enum::COVERAGE_STATUS_TERMINATE;
                            $coverage->save();
                        }else{
                            $acc_corp_eligible = $accmax_cov - $accident_coverage->sum('coverage');
                            if($acc_corp_eligible < $coverage->coverage){
                                $coverage->coverage = ($accmax_cov -$accident_coverage->sum('coverage') );
                                $coverage->save();
                                
                            }
                           
                           }
                       }else{
                        if($coverage->coverage >$accmax_cov){
                           $coverage->coverage = $accmax_cov;
                           $coverage->save();
                        }
                        }
    
                     }


                }
	
                

			}
           
            
        

		 }

        
          
        return [

            'status' => 'success',
            'data' =>['coverages_offered' => $coverages_offered,
                      'corporate_payer' =>$corporate_payer,
                      'si_applicant' =>$si_applicant,
                      ],
            
        ];
	
		}
        
        
        public function checkPds(Request $request)
        {
            $flag = false;
            $profile =$request->user()->profile;
            $payer_id =$request->input('payer_id');
            $skip_pds =$request->input('skip_pds');
            // return [$skip_pds=='false'];
            $coverages_offered =$request->user()->profile->coverages_owner()->where('payer_id',$payer_id)->whereIn('status',['unpaid','increase-unpaid','decrease-unpaid'])->get();
          
                  
            foreach ($coverages_offered as $cov_off){
                $userPdsReviewsCount = UserPdsReview::where('product_id',$cov_off->product_id)->where('individual_id', $profile->id)->get();
             
                //   return [$userPdsReviewsCount->first()];
                    if($userPdsReviewsCount->count() == 0){
                        
                        if($skip_pds == 'true'){
                           
                            $userPdsReviews = new UserPdsReview();
                            $userPdsReviews->product_id = $cov_off->product_id;
                            $userPdsReviews->individual_id = $profile->id;
                            $userPdsReviews->skipped = 0;
                            $userPdsReviews->save();
                            // return $skip_pds;

                            }else{
                                $flag = true;
                            }

                          
                    }
                    else{
                        if($userPdsReviewsCount->first()->skipped == 0){
                            if($skip_pds == 'true'){
                                $userPds=$userPdsReviewsCount->first();
                                $userPds->skipped =1;
                                $userPds->save();
    
                            }
                            else{
                                $flag = true;
                            }
                        }
                    }
            }
            // if($flag){
            //    $read_pds = true;
            // }
    
            // if($skip_pds){
            //     UserPdsReview::where('individual_id', $profile->id)->update([
            //         'skipped'=>1
            //     ]);
            //     $read_pds = false;
            // }
    
            if($flag){
                $read_pds = true;
             }else{
                 if($skip_pds == 'true'){
                     UserPdsReview::where('individual_id', $profile->id)->update([
                         'skipped'=>1
                     ]);
                 } 
                 $read_pds = false;
             }
    
            return [
                'status' => 'success',
                'data' =>['read_pds' => $read_pds
                         ]
                        ];
    
    
    
           
        }

         public function paymentterm(Request $request){

            $user=auth()->user();

            $pay_term = $request->input('pay_term');

            if($pay_term == 'monthly'){
            $pay_da = Coverage::where('owner_id',$user->profile->id)->where('state','active')->latest()->first()->first_payment_on;

            $pay_da_date = Carbon::parse($pay_da);
            $pay_date = $pay_da_date->addYear()->format('Y-m-d');

            }

            $payer_id = Coverage::where('owner_id',$user->profile->id)->where('state','active')->first()->payer_id;
            
            if ($pay_term != NULL){

            $coverage_pay_term = new CoveragePaymentTerm;
            $coverage_pay_term->owner_id = $user->profile->id;
            $coverage_pay_term->pay_term = $pay_term;
            $coverage_pay_term->payer_id = $payer_id;
            $coverage_pay_term->save();
           
            $coverageTypes = CoverageType::where('owner_id', $user->profile->id)->where('payer_id',$user->id)->get();
                            foreach ($coverageTypes as $coverageType) {
                             $coverageType->payment_term_new = $pay_term;
                             $coverageType->save();
                            }

        $u = User::where('id',$user->id)->first();
        $next_date = Coverage::where('owner_id',$user->profile->id)->where('state','active')->latest()->first()->next_payment_on;
        $due = Carbon::parse($next_date)->format('d-m-Y');

    
        $coveragesnew = Coverage::where('owner_id',$user->profile->id)->where('payer_id',$user->id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED,Enum::COVERAGE_STATUS_DECREASE_UNPAID])
                ->get()->filter(function ($item){
                    if($item->next_payment_on!=NULL){
                        $has_decrease =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_DECREASE_UNPAID)->first();
                    if($has_decrease){
                        $has_active = Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                    }else{
                        $has_active =[];
                    }
                    $med_cov =[];
                    if($item->product_id==5){
                        $med_increase =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_ACTIVE_INCREASED)->latest()->first();
                        if($med_increase){
                            $med_cov =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                            $med_cov =array_diff($med_cov,[$med_increase->id]);
                        }else{
                            $med_cov =[];
                        }
                    }
                  
                    if(!in_array($item->id,$med_cov)){
                     if(!in_array($item->id,$has_active)){
                        return $item;
                    }
                }
                    
                    }
                }); 

        $payment_term_unpaid = [];

        if(!empty($coveragesnew)){

        foreach ($coveragesnew as $coverage) {
            $due_date = date('d-m-Y', strtotime($coverage->next_payment_on));
            $due_dates = date('d F Y', strtotime($coverage->next_payment_on));
            $first_pays = date('Y-m-d', strtotime($coverage->first_payment_on));
            $renew = date('Y-m-d', strtotime($coverage->renewal_date)) ?? NULL;
            if ($renew != NULL){
                $renewal =  $renew;
            }else{
                $renewal =  Carbon::parse($coverage->first_payment_on)->addYear();
            }
            $last_pay = date('Y-m-d', strtotime($coverage->last_payment_on)) ?? NULL;
            $productKey = $coverage->product_name;


            if (!isset($payment_term_unpaid[$productKey])) {
                $payment_term_unpaid[$productKey] = [
                    'product_name' => $productKey,
                    'amount_year' => 0,
                    'amount_month' => 0,
                    'due_date' => $due_dates,
                    'amount_balance' => 0,
                    'amount_test'   => 0,
                ];
            }
        
            $userDob = $user->profile->dob;
            $newAge = Carbon::parse($userDob)->diffInYears($due_date);
            $occ_loading = null;
            $latestuw = Coverage::where('owner_id',$user->profile->id)->where('state',Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->uw_id;
            $underwriting=Underwriting::where('id',$latestuw)->first();
            $quote = $coverage->product->getPrice($user->profile,$coverage->coverage,$occ_loading,$newAge,$coverage->product->name == Enum::PRODUCT_NAME_MEDICAL ? $coverage->deductible : null,$underwriting)[0];

            $annually = round($quote,2);
            $month  = round($coverage->product->covertAnnuallyToMonthly($annually),2);

            $diff_months =date_diff(date_create($renewal), date_create($due_dates));
            $diff_monthes =date_diff(date_create($renewal), date_create($first_pays));

            $diff_day = $diff_months->format('%a') / $diff_monthes->format('%a') ;
            $next_payment_to_due_month = $diff_day;


           $amount_balan = round($annually * $next_payment_to_due_month,2);

            $thanks = Thanksgiving::where('individual_id',$user->profile->id)->where('type', 'self')->first()->percentage ?? 0;

            if($thanks != 0) {
            $amount = $amount_balan * $thanks/1000;

            $amount_balance = $amount_balan - $amount;
            $amount_balance = round($amount_balance,2);

            }
            else{
                $amount_balance = $amount_balan;
            }

            if($thanks != 0) {
                $amt = $month * $thanks/1000;

                $monthly = $month - $amt;
                $monthly = round($monthly,2);
            }
            else{
                
                $monthly = $month;
            }

            $payment_term_unpaid[$productKey]['amount_test'] += $amount_balance;
            $payment_term_unpaid[$productKey]['amount_year'] += round($annually,2);
            $payment_term_unpaid[$productKey]['amount_month'] += round($monthly,2);
            $payment_term_unpaid[$productKey]['amount_balance'] = round($payment_term_unpaid[$productKey]['amount_test'],2);     
            
        }
        }

    if ($pay_term == 'monthly') {
        $u->sendNotification('mobile.payment_term_month_title', 'mobile.payment_term_month_text', [
                        'translate_data' => ['name' => $user->profile->name],
                        'buttons' => [
                            ['title' =>__('mobile.ok')],
                        ]
                    ]);

        $monthlyData['title'] = __('web/messages.payment_term_month_title');
        $monthlyData['subject'] = __('web/messages.payment_term_month_subject');
        
        $tableRows = '';

        foreach ($payment_term_unpaid as $productKey => $data) {
            $tableRows .= '<tr>';
            $tableRows .= '<td>' . $productKey . '</td>';
            $tableRows .= '<td>' . $due_date . '</td>';
            $tableRows .= '<td>' . $payment_term_unpaid[$productKey]['amount_month'] . '</td>';
            $tableRows .= '</tr>';
        }

        $emailText = str_replace(':rows', $tableRows, __('web/messages.payment_term_month_text_new', [
            'owner' => $user->profile->name,
        ]));

        $u->notify(new \App\Notifications\Email($emailText, $monthlyData));

    } elseif ($pay_term == 'annually') {
        $u->sendNotification('mobile.payment_term_annual_title', 'mobile.payment_term_annual_text', [
                            'translate_data' => ['name' => $user->profile->name],
                            'buttons' => [
                                ['title' => __('mobile.ok')],
                            ]
                            ]);    
            
        $annualData['title'] = __('web/messages.payment_term_annual_title');
        $annualData['subject'] = __('web/messages.payment_term_annual_subject');

        $tableRows = '';

        foreach ($payment_term_unpaid as $productKey => $data) {
            $tableRows .= '<tr>';
            $tableRows .= '<td>' . $productKey . '</td>';
            $tableRows .= '<td>' . $due_date. '</td>';
            $tableRows .= '<td>' . $payment_term_unpaid[$productKey]['amount_balance'] . '</td>';
            $tableRows .= '</tr>';
        }

        $emailText = str_replace(':rows', $tableRows, __('web/messages.payment_term_annual_text_new', [
            'owner' => $user->profile->name,
        ]));

    $u->notify(new \App\Notifications\Email($emailText, $annualData));
}
        }
    

        return [

            'status' => 'success',
            'data'   =>['payment_term_new' => $pay_term],
            
        ];
    }
    
    public function paymentterm_payer(Request $request){ 
 
 
        $user=auth()->user();

        $user_uuid = $request->input('uuid');

        $pay_term = $request->input('pay_term') ?? null;

        $user_owner = UserModel::where('uuid',$user_uuid)->first()->id;

        $indiv_id = Individual::where('user_id',$user_owner)->first();

        $cov = Coverage::where('owner_id',$indiv_id->id)->where('payer_id',$user->id)->where('state','active')->first();

        if($cov){
        $payment_term_payer = $cov->payment_term;

        $payment_term_new_payer = $cov->payment_term_new;
        }else{
            
            $payment_term_payer = 'monthly';

            $payment_term_new_payer = 'monthly';
        }

        if($cov){
        if($cov->state == 'active'){
            $toggle = 'false';
        }else{
            $toggle = 'true';
        }
      }else{
            $toggle = 'true';
        }

        $payment_term_unpaid = [];

        $te = 0;
        $tee = 0;
        $tes = 0;

        if ($pay_term != NULL){

            $coverage_pay_term = new CoveragePaymentTerm;
            $coverage_pay_term->owner_id = $indiv_id->id;
            $coverage_pay_term->pay_term = $pay_term;
            $coverage_pay_term->payer_id = $user->id;
            $coverage_pay_term->save();
           
            $coverageTypes = CoverageType::where('owner_id', $indiv_id->id)->where('payer_id',$user->id)->get();
                            foreach ($coverageTypes as $coverageType) {
                             $coverageType->payment_term_new = $pay_term;
                             $coverageType->save();
                            }

            }
        $coveragesnew = Coverage::where('owner_id',$indiv_id->id)->where('payer_id',$user->id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED,Enum::COVERAGE_STATUS_DECREASE_UNPAID])
        ->get()->filter(function ($item){
            if($item->next_payment_on!=NULL){
                $has_decrease =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_DECREASE_UNPAID)->first();
            if($has_decrease){
                $has_active = Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
            }else{
                $has_active =[];
            }
            $med_cov =[];
            if($item->product_id==5){
                $med_increase =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->where("status",Enum::COVERAGE_STATUS_ACTIVE_INCREASED)->latest()->first();
                if($med_increase){
                    $med_cov =Coverage::where('owner_id',$item->owner_id)->where('payer_id',$item->payer_id)->where('product_id',$item->product_id)->whereIn("status",[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->get()->pluck('id')->toArray();
                    $med_cov =array_diff($med_cov,[$med_increase->id]);
                }else{
                    $med_cov =[];
                }
            }
          
            if(!in_array($item->id,$med_cov)){
             if(!in_array($item->id,$has_active)){
                return $item;
            }
        }
            
            }
        }); 


    if(!empty($coveragesnew)){

 foreach ($coveragesnew as $coverage) {
    $due_date = date('d-m-Y', strtotime($coverage->next_payment_on));
    $due_dates = date('d F Y', strtotime($coverage->next_payment_on));
    $first_pays = date('Y-m-d', strtotime($coverage->first_payment_on));
    $renew = date('Y-m-d', strtotime($coverage->renewal_date)) ?? NULL;
    if ($renew != NULL){
        $renewal =  $renew;
    }else{
        $renewal =  Carbon::parse($coverage->first_payment_on)->addYear();
    }
    $last_pay = date('Y-m-d', strtotime($coverage->last_payment_on)) ?? NULL;
    $productKey = $coverage->product_name;


    if (!isset($payment_term_unpaid[$productKey])) {
        $payment_term_unpaid[$productKey] = [
            'product_name' => $productKey,
            'amount_year' => 0,
            'amount_month' => 0,
            'due_date' => $due_dates,
            'amount_balance' => 0,
            'amount_test'   => 0,
        ];
    }

    $userDob = $indiv_id->dob;
    $newAge = Carbon::parse($userDob)->diffInYears($due_date);
    $occ_loading = null;
    $latestuw = Coverage::where('owner_id',$indiv_id->id)->where('state',Enum::COVERAGE_STATE_ACTIVE)->latest()->first()->uw_id;
    $underwriting=Underwriting::where('id',$latestuw)->first();
    $quote = $coverage->product->getPrice($indiv_id,$coverage->coverage,$occ_loading,$newAge,$coverage->product->name == Enum::PRODUCT_NAME_MEDICAL ? $coverage->deductible : null,$underwriting)[0];

    $annually = round($quote,2);
    $month  = round($coverage->product->covertAnnuallyToMonthly($annually),2);

    $diff_months =date_diff(date_create($renewal), date_create($due_dates));
    $diff_monthes =date_diff(date_create($renewal), date_create($first_pays));

    $diff_day = $diff_months->format('%a') / $diff_monthes->format('%a') ;
    $next_payment_to_due_month = $diff_day;


   $amount_balan = round($annually * $next_payment_to_due_month,2);

    $thanks = Thanksgiving::where('individual_id',$indiv_id->id)->where('type', 'self')->first()->percentage ?? 0;

    if($thanks != 0) {
    $amount = $amount_balan * $thanks/1000;

    $amount_balance = $amount_balan - $amount;
    $amount_balance = round($amount_balance,2);

    }
    else{
        $amount_balance = $amount_balan;
    }

    if($thanks != 0) {
        $amt = $month * $thanks/1000;

        $monthly = $month - $amt;
        $monthly = round($monthly,2);
    }
    else{
        
        $monthly = $month;
        $monthly = round($monthly,2);
    }

    $data_1[] = [
        'product_name' => $coverage->product_name,
        'amount_year' =>$annually,
        'amount_month' =>$monthly,
        'due_date' => $due_dates,
        'first_pay' => $first_pays,
        'next_payment_to_due_month' => $next_payment_to_due_month,
        'amount_p'    => $amount_balance,
    ];

        if(!empty($data_1)){
        foreach ($data_1 as $item) {
            $te += round($item['amount_p'],2);
        }
    }

        if(!empty($data_1)){
        foreach ($data_1 as $items) {
            $tee += round($items['amount_year'],2);
        }
        }

        if(!empty($data_1)){
        foreach ($data_1 as $items) {
            $tes += round($items['amount_month'],2);
        }

    }

    $payment_term_unpaid[$productKey]['amount_test'] += $amount_balance;
    $payment_term_unpaid[$productKey]['amount_balance'] += round($annually,2);
    $payment_term_unpaid[$productKey]['amount_month'] += round($monthly,2);
    $payment_term_unpaid[$productKey]['amount_year'] = round($payment_term_unpaid[$productKey]['amount_test'],2);     
        
  }
        }

       


    if ($pay_term != NULL){
    
        $u = User::where('id',$user->id)->first();
        $ut = User::where('id',$user_owner)->first();
        $next_date = Coverage::where('owner_id',$indiv_id->id)->where('payer_id',$user->id)->where('state','active')->latest()->first()->next_payment_on;
        $due = Carbon::parse($next_date)->format('d-m-Y');

        if ($pay_term == 'monthly') {
            $ut->sendNotification('mobile.payment_term_month_title_payor', 'mobile.payment_term_month_text_payor', [
                            'translate_data' => ['payor_name' => $user->profile->name],
                            'buttons' => [
                                ['title' =>__('mobile.ok')],
                            ]
                        ]);
    
            $monthlyDataPayor['title'] = __('web/messages.payment_term_month_title_payor');
            $monthlyDataPayor['subject'] = __('web/messages.payment_term_month_subject_payor');
            
            $tableRows = '';
    
            foreach ($payment_term_unpaid as $productKey => $data) {
                $tableRows .= '<tr>';
                $tableRows .= '<td>' . $productKey . '</td>';
                $tableRows .= '<td>' . $due_date . '</td>';
                $tableRows .= '<td>' . $payment_term_unpaid[$productKey]['amount_month'] . '</td>';
                $tableRows .= '</tr>';
            }
    
            $emailTextPayor = str_replace(':rows', $tableRows, __('web/messages.payment_term_month_text_new_payor', [
                'owner' => $indiv_id->name,
                'payor' => $user->profile->name,
            ]));
    
            $u->notify(new \App\Notifications\Email($emailTextPayor, $monthlyDataPayor));

            $monthlyDataOwner['title'] = __('web/messages.payment_term_month_title_owner');
            $monthlyDataOwner['subject'] = __('web/messages.payment_term_month_subject_owner');
            
            $tableRows = '';
    
            foreach ($payment_term_unpaid as $productKey => $data) {
                $tableRows .= '<tr>';
                $tableRows .= '<td>' . $productKey . '</td>';
                $tableRows .= '<td>' . $due_date . '</td>';
                $tableRows .= '<td>' . $payment_term_unpaid[$productKey]['amount_month'] . '</td>';
                $tableRows .= '</tr>';
            }
    
            $emailTextOwner = str_replace(':rows', $tableRows, __('web/messages.payment_term_month_text_new_owner', [
                'owner' => $indiv_id->name,
                'payor' => $user->profile->name,
            ]));
    
            $ut->notify(new \App\Notifications\Email($emailTextOwner, $monthlyDataOwner));
    
        } elseif ($pay_term == 'annually') {
              $ut->sendNotification('mobile.payment_term_annual_title_payor', 'mobile.payment_term_annual_text_payor', [
                            'translate_data' => ['payor_name' => $user->profile->name],
                            'buttons' => [
                                ['title' =>__('mobile.ok')],
                            ]
                        ]);
    
            $monthlyDataPayor['title'] = __('web/messages.payment_term_annual_title_payor');
            $monthlyDataPayor['subject'] = __('web/messages.payment_term_annual_subject_payor');
            
            $tableRows = '';
    
            foreach ($payment_term_unpaid as $productKey => $data) {
                $tableRows .= '<tr>';
                $tableRows .= '<td>' . $productKey . '</td>';
                $tableRows .= '<td>' . $due_date . '</td>';
                $tableRows .= '<td>' . $payment_term_unpaid[$productKey]['amount_year'] . '</td>';
                $tableRows .= '</tr>';
            }
    
            $emailTextPayor = str_replace(':rows', $tableRows, __('web/messages.payment_term_annual_text_new_payor', [
                'owner' => $indiv_id->name,
                'payor' => $user->profile->name,
            ]));
    
            $u->notify(new \App\Notifications\Email($emailTextPayor, $monthlyDataPayor));

            $monthlyDataOwner['title'] = __('web/messages.payment_term_annual_title_owner');
            $monthlyDataOwner['subject'] = __('web/messages.payment_term_annual_subject_owner');
            
            $tableRows = '';
    
            foreach ($payment_term_unpaid as $productKey => $data) {
                $tableRows .= '<tr>';
                $tableRows .= '<td>' . $productKey . '</td>';
                $tableRows .= '<td>' . $due_date . '</td>';
                $tableRows .= '<td>' . $payment_term_unpaid[$productKey]['amount_year'] . '</td>';
                $tableRows .= '</tr>';
            }
    
            $emailTextOwner = str_replace(':rows', $tableRows, __('web/messages.payment_term_annual_text_new_owner', [
                'owner' => $indiv_id->name,
                'payor' => $user->profile->name,
            ]));
    
            $ut->notify(new \App\Notifications\Email($emailTextOwner, $monthlyDataOwner));
    }
    }
        return [

            'status' => 'success',
            'data'   =>['payment_term' => $payment_term_payer, 'payment_term_new' => $payment_term_new_payer , 'toggle_enable' => $toggle ,'payment_term_unpaid_owner' => array_values($payment_term_unpaid) ,'annual_b' =>round($te,2) ?? '','annual' =>round($tee,2) ?? '','month' => round($tes,2) ?? ''],
        ];

    }
    
     public function payor_reject_owner(Request $request){ 

        $user=auth()->user();

        $user_uuid = $request->input('user_id');

        $user_owner = UserModel::where('uuid',$user_uuid)->first()->id;

        $indiv_id = Individual::where('user_id',$user_owner)->first();

        $covs = Coverage::where('owner_id',$indiv_id->id)->where('payer_id',$user->id)->whereIn('status',['unpaid','increase-unpaid'])->get();


        foreach($covs as $cov){

            if($cov->status =='increase-unpaid' ){
            $cov->status = 'increase-terminate';
            }else{
            $cov->status = 'terminate';
            }
            $cov->state = 'inactive';
            $cov->save();

        }

        return [

            'status' => 'success',
            'message' => 'Payor rejected the coverage he offered to owner '
        
        ];
    }

}