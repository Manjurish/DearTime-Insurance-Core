<?php     

namespace App\Http\Controllers\User;

use App\Claim;
use App\Coverage;
use App\Helpers;
use App\Product;
use App\User;
use App\Individual;
use App\UserPdsReview;
use App\ContractExceptions;
use App\UwGroup;
use App\VoucherDetails;
use Carbon\Carbon;
use App\Underwriting;
use File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use PHPUnit\TextUI\Help;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class DocumentController extends Controller
{
    public function docViewIndex(Request $request)
    {
        if ($request->input('type') == 'pds') {
            $request->validate([
                'type' => 'required|in:pds,contract',
                'coverage' => 'required|numeric|min:0|max:5000000',
                'term' => 'required|in:monthly,annually',
            ]);
        } 

        elseif($request->input('type') == 'contract') {
            $request->validate([
                'coverage' => 'required|exists:coverages,uuid',
            ]);
        }

        elseif($request->input('type') == 'consent') {
            $request->validate([
                'claimUuid' => 'required|exists:claims,uuid',
            ]);
        }
        
        elseif($request->input('type') == 'faq') {
            $request->validate([
                'type' => 'required|in:pds,contract,faq',
                'coverage' => 'required|numeric|min:0|max:5000000',
                'term' => 'required|in:monthly,annually',
        ]);
        }
        
        elseif($request->input('type') == 'con') {
            $request->validate([
                'type' => 'required|in:con',
                'coverage' => 'required|numeric|min:0|max:5000000',
                'term' => 'required|in:monthly,annually',
        ]);
        }  

        elseif($request->input('type') == 'dash_faq') {

        }
                
        $mobile = request()->input('app_view');
        $request->request->remove('app_view');

        $request->request->replace([
            'q' => encrypt($request->all())
        ]);
        return $this->generateDoc($request);
        //  return view('user.document_viewer')->with(['queries' => encrypt($request->all()),'mobile' => $mobile]);
    }

    public function generateDoc(Request $request)
    {

        $params = json_decode(json_encode(decrypt($request->q)));
        $user = Auth::user();
        
        if (!empty($params->uuid)) {
            $user = User::WithPendingPromoted()->find(decrypt($params->uuid));
        }

        if (!empty($params->user_id)) {
            $user = User::WithPendingPromoted()->where('uuid', $params->user_id)->first();
        }

        if ($params->type == 'dash_faq') {
            $user_2 = decrypt($params->uuid);
            $user = User::WithPendingPromoted()->whereUuid($user_2)->first();
        }

        //unAuthorized(empty($user));

        $locale = $user->locale;
        $user = $user->profile;
        $encryption = $params->encryption ?? null;
        App::setLocale($locale ?? 'en');
        if ($params->type == 'pds') {
            $req = DocumentController::g_pds($params->p, $user, $params->coverage ?? 1000, $params->term, $locale, $encryption);
        }

        if ($params->type == 'contract') {
            $req = DocumentController::g_contract($params->coverage, $locale, $encryption);
        }

        if ($params->type == 'consent') {
            $req = DocumentController::g_consent($params->claimUuid,$locale, $encryption);
        }

        if ($params->type == 'faq') {
            $req = DocumentController::g_faq($params->p, $user, $params->coverage ?? 1000, $params->term, $locale, $encryption);
        }
        
        if ($params->type == 'con') {
            $req = DocumentController::g_con($params->p, $user, $params->coverage ?? 1000, $params->term, $locale, $encryption);
        }

        if ($params->type == 'dash_faq') {
            $req = DocumentController::dash_faq($params->coverage ?? 1000, $params->term, $locale, $encryption, $params->title);
        }
        
        $payload = [
            'json' => $req,
        ];

        try {
            $response = Http::asJson()->retry(3, 30)->post(env('FACE_API_URL'), $payload);
            Log::info(env('FACE_API_URL'));
            Log::info($payload);
            Log::info($response);
            $file = $response->body();
            //dd($file);
        }catch (\Exception $e){
             dd( $payload);
             Log::error($e->getMessage());
             dd($e->getMessage());
        }

        if (!empty($params->need_save) && $params->type == 'contract') {
            $coverage = Coverage::whereUuid($params->coverage)->first();
            return Helpers::createDocumentFromFile($file, $coverage, $params->type, false, 'pdf');
        }
        if (!empty($params->need_save) && $params->type == 'consent') {
            $claim = Claim::whereUuid($params->claimUuid)->first();
            return Helpers::createDocumentFromFile($file, $claim, $params->type, false, 'pdf');
        }
        return Response::make($file, 200, ['Content-Type' => 'application/pdf']);
    }


    public static function g_pds($p, $user, $coverage, $term, $locale, $encryption = null)
    {
        $age = Carbon::parse($user->dob)->age;
        $product = Product::whereName($p)->first();
        $po = $product->options();
        $max_renewal = $po->max_renewal ?? 99;

        if (!empty($user)) {
            $upr = UserPdsReview::whereProductId($product->id)->where('individual_id', $user->id);
            if ($upr->count() == 0) {
                $upr = new UserPdsReview();
                $upr->product_id = $product->id;
                $upr->individual_id = $user->id;
                $upr->save();

            }
        }


        if($product->name == 'Medical'){
            $total_coverage_amount = Coverage::where('owner_id',$user->id)->where('state','active')->where('product_name','Medical')->latest()->first()->deductible ?? null;
        }else{
        $total_coverage_amount = Coverage::where('owner_id',$user->id)->where('state','active')->where('product_name',$product->name)->sum('coverage');
        }

        $u_premium_table = [];
        $premium_table = [];
        $voucher_check=VoucherDetails::where('email',$user->user->email)->first();
        
        $deductible = 0;

        if ($coverage == 0) {
            $deductible = 0;
        } elseif ($coverage == 1) {
            $deductible = 500;
        } elseif ($coverage == 2) {
            $deductible = 1000;
        } elseif ($coverage == 3) {
            $deductible = 2000;
        } elseif ($coverage == 4) {
            $deductible = 5000;
        } elseif ($coverage == 5) {
            $deductible = 10000;
        }

       $premium_t = [];
       
       $added_premium_array = [];


        if($product->name != 'Medical'){

            $co = Coverage::where('product_name',$product->name)->where('state','active')->where('owner_id',$user->id)->get();

        $added_premium_for_all_ages = [];

        $added_premium = [];

        foreach ($co as $covv) {
            $coveragemodal = Coverage::where('id', $covv->id)->first();
            $campaign_contract =false;
       
            if($coveragemodal->campaign_records==1 && $coveragemodal->corporate_user_status=="accepted")
    {
        $campaign_contract =true;
    }

            $cover =  $coveragemodal->coverage;
            $uww = $coveragemodal->uw_id;
            $uw = Underwriting::where('id',$uww)->first();
            $productModel = Product::where('name', $coveragemodal->product_name)->first();

            if (!empty($voucher_check) && $user->occ == null){
                if($covv->product_name=='Death'){
                $occ_loading = '0';}
                elseif($covv->product_name=='Accident'||$covv->product_name=='Disability'){
                    $occ_loading = '1';}
            }else{
            $occ_loading = null;
            }
         
          
        $premium_table = [];
            for ($i = $age; $i <= $max_renewal; $i++) {
                $premium = Helpers::prepPriceFor(($productModel->getPrice($coveragemodal->covered, $cover, $occ_loading, $i, $coveragemodal->product_name == 'Medical' ? $coveragemodal->deductible : null,$uw,null,null,$campaign_contract)[0]), $term == 'annually'?? 'monthly');
             
               $premium_amount_new = str_replace(',', '', $premium);
               
                if ($premium > 0) {
                    $premium_table[] = [
                        'pt_age' => $i,
                        'pt_amount' => $premium
                    ];
                 
                }

            }
             $premium_t[] = [
                'premium_table' => $premium_table,
                'cover'         => number_format($cover),
            ];
            
         }
           $array_len = count($premium_t);

     for($p = 0; $p <$array_len ; $p++){
         foreach ($premium_t[$p]['premium_table'] as $premium_table) {
                $age = $premium_table['pt_age'];
                $premium_amount = $premium_table['pt_amount'];

                if (is_numeric($age) && is_numeric($premium_amount_new)) {
                    if (!isset($added_premium_for_all_ages[$age])) {
                        $added_premium_for_all_ages[$age] = 0;
                    }
                    $added_premium_for_all_ages[$age] += $premium_amount_new;
                } 
            }
        }

       for($p = 0; $p <$array_len ; $p++){
        foreach ($premium_t[$p]['premium_table'] as $premium_table) {
                $age = $premium_table['pt_age'];
                $premium_amount = $premium_table['pt_amount'];
                
                if (is_numeric($age) && is_numeric(str_replace(',', '', $premium_amount))) {
                    
                    if (!isset($added_premium_array[$age])) {
                        $added_premium_array[$age] = 0;
                    }
                    $added_premium_array[$age] += str_replace(',', '', $premium_amount) / $total_coverage_amount * 1000;
                    
                }
        }
    }

            $added_premium = [];
    foreach ($added_premium_array as $age => $amount) {
     $amount_round = Helpers::round_up($amount,2);
        $decimalCount = strlen(substr(strrchr($amount_round, "."), 1));
        if($decimalCount == 1){
            $amount_round = $amount_round . 0;
        }if($decimalCount == 0){
            $amount_round = $amount_round .'.'. 00;
        }
        $added_premium[] = [
            'pt_age' => $age,
            'pt_amount' => $amount_round
        ];
    }
    }
    else{
        $cot = Coverage::where('product_name','Medical')->where('state','active')->where('owner_id',$user->id)->latest()->first() ?? null;
        if($cot != null){
        $uw = Underwriting::where('id',$cot->uw_id)->first() ?? null;
        $dedu = number_format($cot->coverage) ?? null;

        $campaign_contract =false;
       
            if($cot->campaign_records==1 && $cot->corporate_user_status=="accepted")
    {
        $campaign_contract =true;
    }



        for ($i = $age; $i <= $max_renewal; $i++) {
            $u_premium = Helpers::prepPriceFor(($product->getPrice($user, $cot->coverage, $occ_loading, $i, $cot->product_name == 'Medical' ? $deductible : null,$uw,null,null,$campaign_contract)[0]), $term == 'annually'?? 'monthly');
            if ($u_premium > 0) {
                $premium_t[] = [
                    'pt_age' => $i,
                    'pt_amount' => $u_premium
            ];
            }
            $premium = Helpers::prepPriceFor(($product->getPrice($user, 1000, $occ_loading, $i, $cot->product_name == 'Medical' ? $cot->deductible : null,$uw,null,null,$campaign_contract)[0]),$term == 'annually'?? 'monthly', true);
            if ($premium > 0) {
                $added_premium[] = [
                    'pt_age' => $i,
                    'pt_amount' => $premium
                ];
            }
            }
        }
    }
    
        if ($product->name == Helpers\Enum::PRODUCT_NAME_ACCIDENT) {
            $u_premium_accident_monthly = Helpers::prepPriceFor(($product->getPrice($user, $coverage, $occ_loading, $age, $product->name == 'Medical' ? $deductible : null)[0]), 'monthly');
            $u_premium_accident_annually = Helpers::prepPriceFor(($product->getPrice($user, $coverage, $occ_loading, $age, $product->name == 'Medical' ? $deductible : null)[0]), 'annually');
            $premium_accident_monthly = Helpers::prepPriceFor(($product->getPrice($user, 1000, $occ_loading, $age, $product->name == 'Medical' ? 1000 : null)[0]), 'monthly');
            $premium_accident_annually = Helpers::prepPriceFor(($product->getPrice($user, 1000, $occ_loading, $age, $product->name == 'Medical' ? 1000 : null)[0]), $term == 'annually');
        }

        $doc = 'pds-death.docx';

        if ($product->name == 'Death') {
            $doc = 'PDS-Death Plan-V3.docx';
        } elseif ($product->name == 'Disability') {
            $doc = 'PDS-Disability -V3.docx';
        } elseif ($product->name == 'Accident') {
            $doc = 'PDS-Accident-V3.docx';
        } elseif ($product->name == 'Critical Illness') {
            $doc = 'PDS-Critical Illness-V3.docx';
        } elseif ($product->name == 'Medical') {
            $doc = 'PDS-Medical-V3.docx';
        }
        $res = [
            'bucket' => env('AWS_BUCKET'),
            'encryption' => $encryption,
            'doc' => $doc,
            'locale' => $locale,
            'type' => 'PDS',
            'data' => [
                'coverage_amount' => number_format($coverage, 2),
                'med_cov'          => $dedu ?? null,
                'deductible_amount' => number_format(Helpers::getDeductibleFromCoverage($coverage)),
                'current_date' => Carbon::now()->format('d/m/Y'),
                'u_premium_table' => $premium_t ?? null,
                'premium_table' => $added_premium ?? null,
                //'pt_amount_annual' => Helpers::prepPriceFor(($product->getPrice($user, 1000, $occ_loading, $i, $product->name == 'Medical' ? $coverage : null)[0]), true),
                //'u_pt_amount_annual' => Helpers::prepPriceFor(($product->getPrice($user, $coverage, $occ_loading, $i, $product->name == 'Medical' ? $coverage : null)[0]), true),
               // 'pt_amount_monthly' => Helpers::prepPriceFor(($product->getPrice($user, 1000, $occ_loading, $i, $product->name == 'Medical' ? $coverage : null)[0]), false),
               // 'u_pt_amount_monthly' => Helpers::prepPriceFor(($product->getPrice($user, $coverage, $occ_loading, $i, $product->name == 'Medical' ? $coverage : null)[0]), false),
                'term' => $term == 'monthly' ? __('web/product.monthly') : __('web/product.annually_1'),
                'term_annually' =>$term == 'monthly' ? __('web/product.monthly') : __('web/product.annually_1'),
                'u_premium_accident_monthly' => $u_premium_accident_monthly ?? '',
                'premium_accident_monthly' => $premium_accident_monthly ?? '',
                'u_premium_accident_annually' => $u_premium_accident_annually ?? '',
                'premium_accident_annually' => $premium_accident_annually ?? '',
            ]
        ];

      
       
        return $res;
    }

    public static function g_contract($coverage, $locale, $encryption = null)
    {

         
        $cov = Coverage::whereUuid($coverage)->first();

        

        $uw = $cov->covered->underwritings ?? null;
        empty(($cov) || empty($uw));
        $indv =Individual::where('id',$cov->owner_id)->first();
        
        $next_renewal_date =$cov->payment_term == 'monthly' ? Carbon::parse($cov->first_payment_on)->addYear()->format('Y-m-d') : Carbon::parse($cov->first_payment_on)->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');
        if($next_renewal_date > $today){
            $renewal_date =date("Y-m-d", strtotime("-1 year", strtotime($next_renewal_date)));
            $contract_age = date_diff(date_create($cov->owner->dob), date_create($renewal_date));
            
        }else{
            
            $contract_age = date_diff(date_create($cov->owner->dob), date_create($today));
            
        }

        $allCov = Coverage::where('owner_id', $cov->owner_id)
            ->where('covered_id', $cov->covered_id)
            ->where('product_name', $cov->product_name)
            ->where('state', Helpers\Enum::COVERAGE_STATE_ACTIVE)
            ->orderby('id', 'asc')
            ->get();
            
           
        $all_cov = [];
        $sum_all_cov = [];
        $lastunderwriting_id  =   $uw->id;
        $total_coverage_amount = 0;
        foreach ($allCov as $oneCov) {

            if($oneCov->payer->corporate_type=='payorcorporate'){
                $next_date = $oneCov->payment_term == 'monthly' ? Carbon::parse($oneCov->ndd_payment_due_date ?? now())->format('jS') : Carbon::parse($oneCov->ndd_payment_due_date ?? now())->format('M d');

            }else{
                $next_date = $oneCov->payment_term == 'monthly' ? Carbon::parse($oneCov->next_payment_on ?? now())->format('jS') : Carbon::parse($oneCov->next_payment_on ?? now())->format('M d');

            }
            $all_cov[$oneCov->payer_id]['meta'] = [
                'payer_name' => ($oneCov->sponsored ? 'Deartime Charity Fund':$oneCov->payer->name),                
                'premium_mode' => $oneCov->payment_term == 'monthly' ? __('web/product.monthly') : __('web/product.annually_1'),
                'next_date_format' => $next_date,
                'premium_mode_format' => $oneCov->payment_term == 'monthly' ? __('mobile.month_format') : __('mobile.year_format')
            ];
            if($oneCov->full_premium != NULL){
                $premium = $oneCov->payment_term == 'annually' ? $oneCov->full_premium : $oneCov->full_premium;
            }elseif($oneCov->full_premium == NULL){
                $premium = $oneCov->payment_term == 'annually' ? $oneCov->payment_annually : $oneCov->payment_monthly;
                }
            $all_cov[$oneCov->payer_id]['items'][] = [
                'ref_no' => $oneCov->ref_no,
                'start_date' => Carbon::parse($oneCov->last_payment_on ?? now())->format(config('static.datetime_format')),
                'coverage' => $oneCov->coverage,
                'premium' => round($premium,2),
                'mode' => $oneCov->payment_term == 'monthly' ? __('web/product.monthly') : __('web/product.annually_1'),
                'deductible' => $oneCov->deductible,
            ];
            if(!isset($sum_all_cov[$oneCov->payer_id])) {
                $sum_all_cov[$oneCov->payer_id] = [
                    'coverage' => 0,
                    'premium' => 0,
                    'mode' => '',
                ];
            }
            $sum_all_cov[$oneCov->payer_id]['coverage'] += $oneCov->coverage;
            if($oneCov->full_premium == NULL){
            $sum_all_cov[$oneCov->payer_id]['premium'] += $oneCov->payment_term == 'annually' ? round($oneCov->payment_annually,2) : round($oneCov->payment_monthly,2);
            }else{
            $sum_all_cov[$oneCov->payer_id]['premium'] += $oneCov->payment_term == 'annually' ? round($oneCov->full_premium,2) : round($oneCov->full_premium,2);
            }
            $sum_all_cov[$oneCov->payer_id]['mode'] = $oneCov->payment_term == 'monthly' ? __('web/product.monthly') : __('web/product.annually_1');
            $lastunderwriting_id  =   $oneCov->uw_id;
            $total_coverage_amount += $oneCov->coverage;
        }
       
        $uw = Underwriting::find($lastunderwriting_id) ?? null;
        $sum_all_cov[$oneCov->payer_id]['premium'] = round($sum_all_cov[$oneCov->payer_id]['premium'],2);
        $sum_all_cov['premium'] = round(array_sum(array_column($sum_all_cov, 'premium')), 2);

        $age = Carbon::parse($cov->covered->dob)->age;
        $product = Product::whereName($cov->product_name)->first();
        $po = $product->options();
        $max_renewal = $po->max_renewal ?? 99;
        $term = $cov->payment_term;
        $u_premium_table = [];
        $premium_table = [];
        $voucher_check=VoucherDetails::where('email',$indv->user->email)->first();
        
        $deductible = 0;

        if ($cov->real_coverage == 0) {
            $deductible = 0;
        } elseif ($cov->real_coverage == 1) {
            $deductible = 500;
        } elseif ($cov->real_coverage == 2) {
            $deductible = 1000;
        } elseif ($cov->real_coverage == 3) {
            $deductible = 2000;
        } elseif ($cov->real_coverage == 4) {
            $deductible = 5000;
        } elseif ($cov->real_coverage == 5) {
            $deductible = 10000;
        }

            $premium_t = [];

        if($cov->product_name != 'Medical'){

            $co = Coverage::where('product_name',$cov->product_name)->where('state','active')->where('owner_id',$cov->owner_id)->get();


        $added_premium_for_all_ages = [];

        $added_premium = [];

        foreach ($co as $covv) {
            $coveragemodal = Coverage::where('id', $covv->id)->first();
            $cover =  $coveragemodal->coverage;
            $uww = $coveragemodal->uw_id;
            $uw = Underwriting::where('id',$uww)->first();
            $productModel = Product::where('name', $coveragemodal->product_name)->first();

            $campaign_contract =false;
       
        if($covv->campaign_records==1 && $covv->corporate_user_status=="accepted")
          {
            $campaign_contract =true;
          }

            if (!empty($voucher_check) && $indv->occ == null){
                if($covv->product_name=='Death'){
                $occ_loading = '0';}
                elseif($covv->product_name=='Accident'||$covv->product_name=='Disability'){
                    $occ_loading = '1';}
            }else{
            $occ_loading = null;
            }
         
        $premium_table = [];
            for ($i = $age; $i <= $max_renewal; $i++) {
                $premium = Helpers::prepPriceFor(($productModel->getPrice($coveragemodal->covered, $cover, $occ_loading, $i, $coveragemodal->product_name == 'Medical' ? $coveragemodal->deductible : null,$uw,null,null,$campaign_contract)[0]), $term == 'annually'?? 'monthly');
               $premium_amount_new = str_replace(',', '', $premium);
               
                if ($premium > 0) {
                    $premium_table[] = [
                        'pt_age' => $i,
                        'pt_amount' => $premium
                    ];
                 
                }

            }
            $premium_t[] = $premium_table;
            
         }

        
         foreach ($premium_t as $premium_table) {
            foreach ($premium_table as $premium_row) {
                $age = $premium_row['pt_age'];
                $premium_amount = $premium_row['pt_amount'];
            

                if (is_numeric($age) && is_numeric($premium_amount_new)) {
                    if (!isset($added_premium_for_all_ages[$age])) {
                        $added_premium_for_all_ages[$age] = 0;
                    }
                    $added_premium_for_all_ages[$age] += $premium_amount_new;
                } 
        }
        }
                        
        foreach ($premium_t as $premium_table) {
            foreach ($premium_table as $premium_row) {
                $age = $premium_row['pt_age'];
                $premium_amount = $premium_row['pt_amount'];
              
                if (is_numeric($age) && is_numeric(str_replace(',', '',$premium_amount))) {
                
                    if (!isset($added_premium_array[$age])) {
                        
                        $added_premium_array[$age] = 0;
                    }
                    $added_premium_array[$age] += str_replace(',', '',$premium_amount)/ $total_coverage_amount * 1000;
                    
                }
            }
        }


       
            $added_premium = [];
    foreach ($added_premium_array as $age => $amount) {
     $amount_round = Helpers::round_up($amount,2);
        $decimalCount = strlen(substr(strrchr($amount_round, "."), 1));
        if($decimalCount == 1){
            $amount_round = $amount_round . 0;
        }if($decimalCount == 0){
            $amount_round = $amount_round .'.'. 00;
        }
        $added_premium[] = [
            'pt_age' => $age,
            'pt_amount' => $amount_round
        ];
    }
    }else{
        $cot = Coverage::where('product_name','Medical')->where('state','active')->where('owner_id',$cov->owner_id)->latest()->first();
        $uw = Underwriting::where('id',$cot->uw_id)->first();
        $campaign_cot =false;
        if($cot->campaign_records==1 && $cot->corporate_user_status=="accepted")
{
    $campaign_cot =true;
}



        for ($i = $age; $i <= $max_renewal; $i++) {
            $premium = Helpers::prepPriceFor(($product->getPrice($cot->covered, $cot->coverage, $occ_loading, $i, $cot->product_name == 'Medical' ? $cot->deductible : null,$uw,null,null,$campaign_cot)[0]), $term == 'annually'?? 'monthly');
            if ($premium > 0) {
                $premium_t[] = [
                    'pt_age' => $i,
                    'pt_amount' => $premium
                ];
    }
    $u_premium = Helpers::prepPriceFor(($product->getPrice($cot->covered, 1000, $occ_loading, $i, $cot->product_name == 'Medical' ? $cot->deductible : null,$uw,null,null,$campaign_cot)[0]),$term == 'annually'?? 'monthly', true);
    if ($u_premium > 0) {
        $added_premium[] = [
            'pt_age' => $i,
            'pt_amount' => $u_premium
        ];
    }
    }
    $sum_all_cov['premium_medi'] = Helpers::prepPriceFor(($product->getPrice($cot->covered, $cot->coverage, $occ_loading, $age, $cot->product_name == 'Medical' ? $cot->deductible : null,$uw,null,null,$campaign_cot)[0]), $term == 'annually'?? 'monthly');
    
    }
    
    $cov_d = Coverage::whereUuid($coverage)->first();

     $user_id =Individual::where('id',$cov_d->owner_id)->first()->user_id;

       if($cov_d->payer_id != $user_id){
        $premium_payment = __('mobile.payer_online');
      }elseif($cov_d->payer_id == $user_id && $cov_d->payer_id != $user_id){
        $premium_payment = __('mobile.owner_card');
      }elseif($cov_d->payer_id == $user_id){
        $premium_payment = __('mobile.owner_card');
      }

          /*  for ($i = $age; $i <= $max_renewal; $i++) {
            $premium = Helpers::prepPriceFor(($product->getPrice($cov->covered, $cov->coverage, $occ_loading, $i, $cov->product_name == 'Medical' ? $oneCov->deductible : null)[0]), $term == 'annually'?? 'monthly');
            if ($premium > 0) {
                $premium_table[] = [
                    'pt_age' => $i,
                    'pt_amount' => $premium
                ];
            }
            $u_premium = Helpers::prepPriceFor(($product->getPrice($cov->covered, 1000, $occ_loading, $i, $cov->product_name == 'Medical' ? $oneCov->deductible : null)[0]),$term == 'annually'?? 'monthly', true);
            if ($u_premium > 0) {
                $u_premium_table[] = [
                    'pt_age' => $i,
                    'pt_amount' => $u_premium
                ];
            }
        } */

        $nominees = [];
        $is_nominee_charity = false;
        $nominee_charity_percent = 0;
        foreach ($cov->covered->beneficiaries as $beneficiary) {
            if ($beneficiary->email != 'Charity@Deartime.com') {
            $beneficiaryage = Carbon::parse($beneficiary->nominee_id ? $beneficiary->nominee->dob :$beneficiary->dob)->age;
            if($beneficiary->nominee_id ? $beneficiary->nominee->nationality=='Malaysian':$beneficiary->nationality=='135'){
                if($beneficiaryage < 12){
               $label =__('mobile.MyKid');
               }else{
                $label =__('mobile.nominee_MyKad');
               }
           }else{
               $label =__('mobile.nominee_passport');
           }

           $relationship = '';
           if ($beneficiary->relationship == 'sibling') {
               $relationship = __('mobile.sibling');
           } elseif ($beneficiary->relationship == 'child') {
               $relationship = __('mobile.child');
           } elseif ($beneficiary->relationship == 'spouse') {
               $relationship = __('mobile.spouse');
           } elseif ($beneficiary->relationship == 'parent') {
               $relationship = __('mobile.parent');
           } elseif ($beneficiary->relationship == 'parent_in_law') {
               $relationship = __('mobile.parent_in_law');
           } elseif ($beneficiary->relationship == 'sibling_in_law') {
               $relationship = __('mobile.sibling_in_law');
           } elseif ($beneficiary->relationship == 'grandparent') {
               $relationship = __('mobile.grandparent');
           } elseif ($beneficiary->relationship == 'grandchildren') {
               $relationship = __('mobile.grandchildren');
           } elseif ($beneficiary->relationship == 'other') {
               $relationship = __('mobile.other');
           }
           

                $nominees[] = [
                    'nominee_percent' => $beneficiary->percentage . '%',
                    'nominee_name' => $beneficiary->nominee_id ? $beneficiary->nominee->name : $beneficiary->name,
                    'nominee_email' => $beneficiary->nominee_id ? $beneficiary->nominee->user->email : $beneficiary->email,
                    'nominee_passport_mykad' =>$label ,
                    'nominee_nric' => $beneficiary->nominee_id ? $beneficiary->nominee->nric :  $beneficiary->nric ?? '',
                    'nominee_status' => $beneficiary->status == 'registered' ? __('mobile.registered') : $beneficiary->status ?? '',
                    'nominee_relation' => $relationship ?? '',
                ];
            } else {
                $is_nominee_charity = true;
                $nominee_charity_percent = $beneficiary->percentage;
            }
        }

        $trustees = [];
        foreach ([] as $trustee) {
            $trustees[] = [
                'trustee_name' => '',
                'trustee_email' => '',
                'trustee_passport_mykad' => '',
                'trustee_nric' => '',
                'trustee_status' => '',
            ];
        }


        if ($product->name == 'Death') {
            $doc =  $campaign_contract ? 'Contract - Death-CPGV1.docx' : 'Contract - Death-V5.docx';


        } elseif ($product->name == 'Disability') {
            $doc = 'Contract - Disability-V5.docx';
        } elseif ($product->name == 'Accident') {
            $doc = 'Contract - Accident-V5.docx';
        } elseif ($product->name == 'Critical Illness') {
            $doc = 'Contract - Critical Illness-V5.docx';
        } elseif ($product->name == 'Medical') {
            $doc = 'Contract - Medical-V5.docx';
        }

        // TODO : Change this when contracts are ready
        //$doc = 'Contract - Medical.docx';
        $uwg = UwGroup::get();
        $out = [];
        $underwriting_date = '';
        $exceptions=[];
        $answers = $uw->answers;

        $contract_exp =ContractExceptions::where('id',1)->first();
        
        // dd($contract_exp->ci_exp_en);
        if ($product->name == 'Critical Illness'){
            $num =0;
               
                if($locale == 'en'){
                    foreach ($contract_exp->ci_exp_en['exceptions'] as $exp){
                        $num +=1;
                        $exceptions[]= $num.'. '.$exp;
                    }
                }
                elseif($locale == 'bm'){
                    foreach ($contract_exp->ci_exp_bm['exceptions'] as $exp){
                        $num +=1;
                        $exceptions[]= $num.'. '.$exp;
                    }
                }
                elseif($locale == 'ch'){
                    foreach ($contract_exp->ci_exp_ch['exceptions'] as $exp){
                        $num +=1;
                        $exceptions[]= $num.'. '.$exp;
                    }
                }
               
       }elseif ($product->name == 'Medical') {
           $num =0;
           if($locale == 'en'){
            foreach ($contract_exp->medical_exp_en['exceptions'] as $exp){
                $num +=1;
                $exceptions[]= $num.'. '.$exp;
            }
        }
        elseif($locale == 'bm'){
            foreach ($contract_exp->medical_exp_bm['exceptions'] as $exp){
                $num +=1;
                $exceptions[]= $num.'. '.$exp;
            }
        }
        elseif($locale == 'ch'){
            foreach ($contract_exp->medical_exp_ch['exceptions'] as $exp){
                $num +=1;
                $exceptions[]= $num.'. '.$exp;
            }
        }
       }
       
       
       $startdate = Carbon::parse($cov->first_payment_on)->format(config('static.date_format'));

        if ($product->name == 'Critical Illness'){
             $num =8; 
            foreach ($answers['answers'] as $answer){
                $ans =\App\Uw::find($answer);
                
                if(!empty($ans->critical_en)){
                    $num +=1;
                
                 if($locale == 'en'){
                    $exceptions[] = $num . '. ' .$ans->critical_en. ' '.__('mobile.contract_exp',['startdate' => $startdate]);
                }
                 elseif($locale == 'bm'){
                    $exceptions[]= $num.'. '.$ans->critical_bm. ' ' . __('mobile.contract_exp',['startdate' => $startdate]);
                 }
                 elseif($locale == 'ch'){
                    $exceptions[]= $num.'. '.$ans->critical_ch. ' ' . __('mobile.contract_exp',['startdate' => $startdate]);  
                 }
             } 
             
            }
       } elseif ($product->name == 'Medical') {
            $num =21;
            foreach ($answers['answers'] as $answer){
                $ans =\App\Uw::find($answer);
                //$num +=1;
                if(!empty($ans->medical_en)){
                    $num +=1;
                    if($locale == 'en'){
                        $exceptions[]= $num.'. '.$ans->medical_en. ' '.__('mobile.contract_exp',['startdate' => $startdate]);
                     }
                     elseif($locale == 'bm'){
                        $exceptions[]= $num.'. '.$ans->medical_bm. ' '.__('mobile.contract_exp',['startdate' => $startdate]);
                     }
                     elseif($locale == 'ch'){
                        $exceptions[]= $num.'. '.$ans->medical_ch. ' '.__('mobile.contract_exp',['startdate' => $startdate]);
                     }
                    
                   }
                
            }
        }

        
        foreach ($uwg as $uwa) {
            $qs = [];
            foreach ($uwa->questions()->whereIn("id", $uw->answers['answers'] ?? [])->get() as $question) {
               // $qs[] = ['title' => $question->title];
               
               $ts='';
               $tm='';
               $tz='';
               
               $titles = DB::select('SELECT title , title_bm, title_zh
                FROM uws
                WHERE parent_uws_id = ? AND id IN (' . implode(',', $uw->answers['answers'] ?? []) . ')
                ORDER BY title;', [$question->id]);
                foreach ($titles as $title) {
                $ts=$ts.$title->title . ",";
                $tm=$tm.$title->title_bm. ",";
                $tz=$tz.$title->title_zh. ",";
                
                }
               
             if($locale == 'en'){
               $qs[] = ['title' => $question->title." ".$ts];    
             }
             elseif($locale == 'bm'){
                $qs[] = ['title' => $question->title_bm." ".$tm];  
             }
             elseif($locale == 'ch'){
                $qs[] = ['title' => $question->title_zh." ".$tz];  
             }
            }
            if (count($qs) > 0) {
              
                $underwriting_date = $uw->created_at;
                switch ($uwa->name) {
                    case 'health':
                        $out [] = [
                            'title' => __('web/medicalsurvey.contract_health'),
                            'qs' => $qs,
                        ];
                        break;
                    case 'health2':
                        $out [] = [
                            'title' => __('web/medicalsurvey.contract_health2'),
                            'qs' => $qs,
                        ];
                        break;
                    case 'family':
                        $out [] = [
                            'title' => __('web/medicalsurvey.contract_family'),
                            'qs' => $qs,
                        ];
                        break;
                    case 'lifestyle':
                        $out [] = [
                            'title' => __('web/medicalsurvey.contract_lifestyle'),
                            'qs' => $qs,
                        ];
                        break;
                    case 'new1':
                        $out [] = [
                            'title' => __('web/medicalsurvey.contract_new1'),
                            'qs' => $qs,
                        ];
                        break;
                    case 'new2':
                        $out [] = [
                            'title' => __('web/medicalsurvey.contract_new2'),
                            'qs' => $qs,
                        ];
                        break;
                    case 'new3':
                        $out [] = [
                            'title' => __('web/medicalsurvey.contract_new3'),
                            'qs' => $qs,
                        ];
                        break;
                    case 'new4':
                        $out [] = [
                            'title' => __('web/medicalsurvey.contract_new4'),
                            'qs' => $qs,
                        ];
                        break;


                }
            }
        }
        $res = [
            'bucket' => env('AWS_BUCKET'),
            'encryption' => $encryption,
            'doc' => $doc,
            'locale' => $locale,
            'type' => 'Contract',
            'data' => [
                'payer_name' => ($indv->is_charity())? 'DearTime Charity Fund':($cov->payer->name ?? ''),
                'premium_mode' => $cov->payment_term == 'monthly' ?__('web/product.monthly') :__('web/product.annually_1'),
//                'premium_mode' => $locale == 'ch'?$cov->payment_term == 'monthly'?__('web/product.monthly'):__('web/product.annually'):ucfirst($cov->payment_term == 'monthly'?__('web/product.monthly'):__('web/product.annually')),
                'premium_amount' => $cov->Payable ?? '',
                'premium_payment' => $premium_payment,
                'created_at' => Carbon::parse($cov->first_payment_on)->format(config('static.datetime_format')),
                'next_date' => Carbon::parse($cov->next_payment_on ?? now())->format(config('static.date_format')),
                'next_date_format' => $cov->payment_term == 'monthly' ? __('mobile.'.strtolower(Carbon::parse($cov->next_payment_on ?? now())->format('M')))." ".(Carbon::parse($cov->next_payment_on ?? now())->format('d')) :  __('mobile.'.strtolower(Carbon::parse($cov->next_payment_on ?? now())->format('M')))." ".(Carbon::parse($cov->next_payment_on ?? now())->format('d')),
                'renewal_date_format' => $cov->payment_term == 'monthly' ?__('mobile.'.strtolower(Carbon::parse($cov->first_payment_on)->addYear()->format('M')))." ".Carbon::parse($cov->first_payment_on)->addYear()->format('d') :__('mobile.'.strtolower(Carbon::parse($cov->next_payment_on ?? now())->format('M')))." ".Carbon::parse($cov->next_payment_on ?? now())->format('d'),
                'premium_mode_format' => $cov->payment_term == 'monthly' ? __('mobile.month_format') : __('mobile.year_format'),
                'covered_name' => $cov->covered->name ?? '',
                'covered_passport_mykad' => $cov->covered && $cov->covered->is_local() ? __('mobile.MyKad') : __('mobile.passport'),
                'covered_nric' => $cov->covered->nric ?? '',
                'covered_passport_expire' => ($cov->covered && !$cov->covered->is_local()) ? Carbon::parse($cov->covered->passport_expiry_date ?? now())->format(config('static.date_format')) : '',
                'covered_dob' => Carbon::parse($cov->covered->dob ?? now())->format(config('static.date_format')),
                'covered_age' => !empty($cov->covered) ? ($contract_age->format('%y') ?? '') : '',
                'covered_gender' => $cov->covered->gender == 'male' ? __('web/profile.male') : __('web/profile.female'),
                'covered_nationality' => $cov->covered->nationality ?__('mobile.malaysian') : '',
                'covered_mobile' => $cov->covered->mobile ?? '',
                'covered_email' => $cov->covered->user->email ?? '',
                'covered_address' =>$cov->covered->address->address1 . '-' . $cov->covered->address->address2 . '-' . $cov->covered->address->address3 . '-' . $cov->covered->address->address_postcode->name . '-' . $cov->covered->address->address_city->name . '-' . $cov->covered->address->address_state->name ?? '',
                'covered_local' => ($cov->covered && !$cov->covered->is_local()),
                'owner_name' => $cov->owner->name ?? '',
                'owner_passport_mykad' => $cov->owner && $cov->owner->is_local() ? __('mobile.MyKad') : __('mobile.passport'),
                'owner_nric' => $cov->owner->nric ?? '',
                'owner_passport_expire' => ($cov->owner && $cov->owner->is_local()) ? Carbon::parse($cov->owner->passport_expiry_date ?? now())->format(config('static.date_format')) : '',
                'owner_dob' => Carbon::parse($cov->owner->dob ?? now())->format(config('static.date_format')),
                'owner_age' => !empty($cov->owner) ? ($cov->owner->age() ?? '') : '',
                'owner_gender' => $cov->owner->gender == 'male' ? __('web/profile.male') : __('web/profile.female'),
                'owner_nationality' => $cov->owner->nationality ?? '',
                'owner_mobile' => $cov->owner->mobile ?? '',
                'owner_email' => $cov->owner->user->email ?? '',
                'owner_address' =>$cov->owner->address1 . '-' . $cov->owner->address->address2 . '-' . $cov->owner->address->address3 . '-' . $cov->owner->address->address_postcode->name . '-' . $cov->owner->address->address_city->name . '-' . $cov->owner->address->address_state->name ?? '',
                'owner_relation' => '',
                'covered_owner' => $cov->covered_id == $cov->owner_id,
                'n_covered_owner' => $cov->covered_id != $cov->owner_id,
                'owner_local' => ($cov->owner && !$cov->owner->is_local()),
                'deductible' => number_format($oneCov->deductible),
                'medical_survey_date' => Carbon::parse($uw->created_at ?? now())->format(config('static.date_format')),
                'height' => $uw->answers['height'] ?? 0,
                'weight' => $uw->answers['weight'] ?? 0,
                'cigarette' => $uw->answers['smoke'] ?? 0,
                'underwritings' => $out,
                'exceptions'=>$exceptions,
                'u_premium_table' => $added_premium,
                'premium_table' => $premium_t,
                'total_coverage_amount' => $total_coverage_amount,
                'first_time_purchase_date' => Carbon::parse($cov->first_payment_on)->format(config('static.datetime_format')),
                'nominees' => $nominees,
                'is_nominee_charity' => $is_nominee_charity,
                'nominee_charity_percent' => $nominee_charity_percent . '%',
                'underwriting_date' => Carbon::parse($underwriting_date)->format(config('static.datetime_format')),
                'trustees' => $trustees,
                'has_trustees' => count($trustees) > 0,
                'all_cov' => $all_cov,
                'sum_all_cov' => $sum_all_cov,
                'show_total_all_cov' => count($all_cov) > 0,
                'has_nominees' => $nominee_charity_percent != 100,
            ]
        ];

       
        
        return $res;
    }

    /****************************** FAQ ****************************************/

 public static function g_faq($p, $user, $coverage, $term, $locale, $encryption = null)
    {
        $product = Product::whereName($p)->first();

        if ($product->name == 'Death') {
            $doc = 'FAQ-Death.docx';
        } elseif ($product->name == 'Disability') {
            $doc = 'FAQ-Disability.docx';
        } elseif ($product->name == 'Accident') {
            $doc = 'FAQ-Accident.docx';
        } elseif ($product->name == 'Critical Illness') {
            $doc = 'FAQ-Critical Illness.docx';
        } elseif ($product->name == 'Medical') {
            $doc = 'FAQ-Medical.docx';
        }
        $res = [
            'bucket' => env('AWS_BUCKET'),
            'encryption' => $encryption,
            'doc' => $doc,
            'locale' => $locale,
            'type' => 'FAQ',
            'data' => [
            ]
        ];
        return $res;
    }
    
    /****************************** Contract ****************************************/

   public static function g_con($p, $user, $coverage, $term, $locale, $encryption = null)
        {
    
            $product = Product::whereName($p)->first();
    
            if ($product->name == 'Death') {
                $doc = 'Contract-Death.docx';
            } elseif ($product->name == 'Disability') {
                $doc = 'Contract-Disability.docx';
            } elseif ($product->name == 'Accident') {
                $doc = 'Contract-Accident.docx';
            } elseif ($product->name == 'Critical Illness') {
                $doc = 'Contract-Critical Illness.docx';
            } elseif ($product->name == 'Medical') {
                $doc = 'Contract-Medical.docx';
            }
           
            $res = [
                'bucket' => env('AWS_BUCKET'),
                'encryption' => $encryption,
                'doc' => $doc,
                'locale' => $locale,
                'type' => 'CON',
                'data' => [
                    'premium_mode' =>$term == 'monthly' ? __('web/product.monthly') : __('web/product.annually_1'),
                ]
            ];
            return $res;
        }

/****************************** Contract ****************************************/

public static function g_consent($claimUuid,$locale, $encryption = null){
        $claim = Claim::where('uuid',$claimUuid)->first();
        empty(($claim));
        $doc = 'consent-form.docx';
        $res = [
            'bucket' => env('AWS_BUCKET'),
            'encryption' => $encryption,
            'doc' => $doc,
            'locale' => $locale,
            'type' => 'consent',
            'data' => [
                'name_of_claimant' => $claim->claimant_name ?? 'ali',
                'claimant_nric' => $claim->profile->nric,
                'claimant_is_foreign' => $claim->profile->is_local(),
                'claimant_date' => Carbon::parse($claim->created_at)->format(config('static.datetime_format')),
                'name_of_insured' => $claim->owner_name,
                'insured_nric' => $claim->owner->nric,
                'insured_is_foreign' => $claim->owner->is_local(),
                'now' => Carbon::now()->format(config('static.datetime_format')),
            ]
        ];
        return $res;
    }

/**************  Dash Board FAQ Test  *************************************/

public static function dash_faq($coverage, $term, $locale, $encryption = null, $title)
{

        $doc = $title;        

        $res = [
            'bucket' => env('AWS_BUCKET'),
            'encryption' => $encryption,
            'doc' => $doc,
            'locale' => $locale,
            'type' => 'Dashboardfaqs',
            'data' => [
            ]
        ];
        return $res;
}

/**************  Dash Board FAQ Test  *************************************/


    public function downloadResource($path)
    {
        $path = decrypt($path);

        if (!File::exists(resource_path($path))) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        $path = resource_path($path);

        $file = File::get($path);
        $type = File::mimeType($path);
        $fileName = File::basename($path);

        $response = Response::make($file, 200);
        $response->header('X-Vapor-Base64-Encode', 'true');
        $response->header('Content-Type', $type);
        $response->header('Content-Disposition: attachment; filename=', $fileName);

        return $response;
    }
}