<?php

namespace App\Jobs;

use App\Helpers;
use App\User;
use App\UserModel;
use App\Individual;
use App\Helpers\Enum;
use App\Coverage;
use App\Action;
use App\CoverageThanksgiving;
use App\Order;
use App\State;
use App\City;
use App\Transaction;
use App\Thanksgiving;
use App\Underwriting;
use App\Credit;
use App\SpoCharityFunds;
use App\CoverageOrder;
use App\VoucherCampaignUpload;
use App\PostalCode;
use App\VoucherDetails;
use App\VoucherCode;
use App\VoucherCampaign;
use App\Address;
use App\Product;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\InternalUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mmeshkatian\Ariel\BaseController;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArecaAutoUpload implements ShouldQueue
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
        //$testusers = VoucherDetails::where('created','<>',1)->whereDate('created_at','=',Carbon::now()->startOfDay())->get();
         $testusers = VoucherDetails::where('created','<>',1)->whereDate('created_at','=',Carbon::now()->startOfDay())->get()->filter( function($items){
            $campaign=Vouchercode::where('voucher_code',$items->voucher_code )->first();
            if( $campaign->campaign_id == 1){
            return $items;
            }
        });
        //dd(Carbon::now()->startOfDay());
        //>format('Y-m-d')
        if($testusers!=null){  
          
        foreach($testusers as $testuser){
        //try { 
            $email = $testuser->email;

            $existemail = UserModel::where('email',$testuser->email)->first() ?? null;

            $mobile = $testuser->mobile;

            $existmobile = Individual::where('mobile',$testuser->mobile)->first() ?? null;

            $nric = $testuser->nric;

            $existnric = Individual::where('nric',$testuser->nric)->first() ?? null;

            $voucher_cam =VoucherCode::where('voucher_code',$testuser->voucher_code)->first()->campaign_id;

            $voucher_ca = VoucherCampaign::where('id',$voucher_cam)->first()->campaign_email;

            $payer_id = UserModel::where('email',$voucher_ca)->first()->id;

            if($existnric != null){

                // $u_nric = Individual::where('nric',$testuser->nric)->first();

                // dd($existnric);

                 if($mobile == $existnric->mobile){

                    $user_de = UserModel::where('id',$existnric->user_id)->first();

                    if($email == $user_de->email){

                        $products = Product::all();

                        foreach ($products as $product){

                            if($product->id == 1){
                             
                            $covv = Coverage::where('owner_id',$existnric->id)->where('product_name','Death')->where('state','active')->get() ?? null;


                           
                                $payment_term = 'annually';
                                $payment_term_new = 'annually';
                                $owner_id = $existnric->id;
                                $payer_id = $payer_id;
                                $covered_id = $existnric->id;
                                $product_id = 1;
                                $product_name = 'Death';
                                if($covv->isNotEmpty()){
                                    $status = 'increase-unpaid';

                                }else{
                                    $status ='unpaid';
                                }
                                $coverage = 20000;
                                $payment_annually = 0;
                                $full_premium = 0;
                                $payment_monthly = 0;
                                $without_loading = 0;
                                $corporate_user_status='accepted';
                                $campaign_records=1;

                                $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading,$corporate_user_status,$campaign_records);
                            
                        } 
                            if($product->id == 3){
                             
                                $covv = Coverage::where('owner_id',$existnric->id)->where('product_name','Accident')->where('state','active')->get() ?? null;
    
                             
                                    $payment_term = 'annually';
                                    $payment_term_new = 'annually';
                                    $owner_id = $existnric->id;
                                    $payer_id = $payer_id;
                                    $covered_id = $existnric->id;
                                    $product_id = 3;
                                    $product_name = 'Accident';
                                    if($covv->isNotEmpty()){
                                        $status = 'increase-unpaid';
    
                                    }else{
                                        $status ='unpaid';
                                    }
                                    
                                    $coverage = 20000;
                                    $payment_annually = 0;
                                    $full_premium = 0;
                                    $payment_monthly = 0;
                                    $without_loading = 0;
                                    $corporate_user_status='accepted';
                                    $campaign_records=1;
    
                                    $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading,$corporate_user_status,$campaign_records);
                                

                        }

                        if($product->id == 2){
                             
                            $covv = Coverage::where('owner_id',$existnric->id)->where('product_name','Disability')->where('state','active')->get() ?? null;

                           
                                $payment_term = 'annually';
                                $payment_term_new = 'annually';
                                $owner_id = $existnric->id;
                                $payer_id = $payer_id;
                                $covered_id = $existnric->id;
                                $product_id = 2;
                                $product_name = 'Disability';
                                if($covv->isNotEmpty()){
                                    $status = 'increase-unpaid';

                                }else{
                                    $status ='unpaid';
                                }
                                
                                $coverage = 100000;
                                $payment_annually = 0;
                                $full_premium = 0;
                                $payment_monthly = 0;
                                $without_loading = 0;
                                $corporate_user_status='accepted';
                                $campaign_records=1;

                                $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading,$corporate_user_status,$campaign_records);
                            
                    }


                    }

               }


            }

        }elseif($existemail == null && $existmobile == null && $existnric == null) {

            $adduser = new User();
            $adduser->type = 'individual';
            $adduser->email =  $email;
            $adduser->locale = 'en';
            $adduser->password = '$2y$10$ORvHrpN6uzOPDra0MWkhiuGawoC.YDZve3zm31TRcWTPgtKRZK4ia';
            $adduser->save();

            $adduser->password = '';
            $adduser->save();

            $address =new Address();
            $address->address1 = $testuser->residential_address;
            $address->city = $testuser->city;
            $address->postcode = $testuser->zipcode;
            $address->state = $testuser->state;
            $address->country = $testuser->country;
            $address->save();

            $indv_user = new Individual();
            $indv_user->user_id = $adduser->id;
            $indv_user->nric = $testuser->nric;
            $indv_user->name =$testuser->name;
            $indv_user->gender =$testuser->gender;
            $indv_user->dob = $testuser->dob;
            $indv_user->mobile = $testuser->mobile;
            $indv_user->nationality =$testuser->nationality;
            $indv_user->address_id = $address->id;
            if($testuser->other_life_insurance == 'yes'){
            $indv_user->has_other_life_insurance = 1;
            }else{
            $indv_user->has_other_life_insurance=0;
        }

        if($testuser->country == 'Malaysia'){
            $indv_user->country_id = 135;
        }else{
            $indv_user->country_id = null;
        }
        $indv_user->save();

        $products = Product::all();

        foreach ($products as $product){

            if($product->id == 1){
             
            $covv = Coverage::where('owner_id',$indv_user->id)->where('product_name','Death')->where('state','active')->get() ?? null;

           
                $payment_term = 'annually';
                $payment_term_new = 'annually';
                $owner_id = $indv_user->id;
                $payer_id = $payer_id;
                $covered_id = $indv_user->id;
                $product_id = 1;
                $product_name = 'Death';
                if($covv->isNotEmpty()){
                    $status = 'increase-unpaid';

                }else{
                    $status ='unpaid';
                }
                $coverage = 20000;
                $payment_annually = 0;
                $full_premium = 0;
                $payment_monthly = 0;
                $without_loading = 0;
                $corporate_user_status='accepted';
                $campaign_records=1;

                $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading,$corporate_user_status,$campaign_records);
               
        } 
            if($product->id == 3){
             
                $covv = Coverage::where('owner_id',$indv_user->id)->where('product_name','Accident')->where('state','active')->get() ?? null;

             
                    $payment_term = 'annually';
                    $payment_term_new = 'annually';
                    $owner_id = $indv_user->id;
                    $payer_id = $payer_id;
                    $covered_id = $indv_user->id;
                    $product_id = 3;
                    $product_name = 'Accident';
                    if($covv->isNotEmpty()){
                        $status = 'increase-unpaid';

                    }else{
                        $status ='unpaid';
                    }
                    
                    $coverage = 20000;
                    $payment_annually = 0;
                    $full_premium = 0;
                    $payment_monthly = 0;
                    $without_loading = 0;

                    $corporate_user_status='accepted';
                    $campaign_records=1;

                    $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading,$corporate_user_status,$campaign_records);
                

        }

        if($product->id == 2){
             
            $covv = Coverage::where('owner_id',$indv_user->id)->where('product_name','Disability')->where('state','active')->get() ?? null;

           
                $payment_term = 'annually';
                $payment_term_new = 'annually';
                $owner_id = $indv_user->id;
                $payer_id = $payer_id;
                $covered_id = $indv_user->id;
                $product_id = 2;
                $product_name = 'Disability';
                if($covv->isNotEmpty()){
                    $status = 'increase-unpaid';

                }else{
                    $status ='unpaid';
                }
                
                $coverage = 100000;
                $payment_annually = 0;
                $full_premium = 0;
                $payment_monthly = 0;
                $without_loading = 0;

                $corporate_user_status='accepted';
                $campaign_records=1;

                $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading,$corporate_user_status,$campaign_records);
            
    }

    }
    

    }

	// }catch (\Exception $e) {
	// 	echo 'Exception:';
	// 	dd($e->getMessage());
	// }


		//try { 

		
		
        $covs = Coverage::where('owner_id',$indv_user->id)->where('payer_id',$payer_id)->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_INCREASE_UNPAID])->get();
		// dd($datas);
	
	$thanks = Thanksgiving::where('individual_id',$indv_user->id)->latest()->first();
	if(empty($thanks)){
		$thanks = new Thanksgiving();
		$thanks->individual_id = $indv_user->id;
		$thanks->type = 'charity';
		$thanks->percentage = 100;
		$thanks->save();
	}
	
    
	// if(empty($uw)){
   	    $uw = new Underwriting();
		$uw->individual_id = $indv_user->id;
    	$answers = json_encode(['weight' => 70, 'height' => 175, 'smoke' => 0, 'answers' => [34, 53, 39, 52, 57, 59, 61]]);
		$uw->answers = json_decode($answers, true);
		$uw->death = 1;
		$uw->disability = 1;
		$uw->ci = 1;
		$uw->medical = 1;
		$uw->created_by = $indv_user->user_id;
		$uw->sio_answers = json_encode(['weight' => 70, 'height' => 175, 'smoke' => 0, 'answers' => [34, 53, 39, 52, 57, 59, 61]]);
		$uw->save();
	// }
	

	foreach($covs as $cov){
	$cov->update([
		'uw_id'=>$uw->id
	]);
 }

   
 


	$testuser->created=1;
	$testuser->save();

    

}

	

	}
}

private function createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading,$corporate_user_status,$campaign_records)
    {
		return Coverage::create([
			'owner_id'         => $owner_id,
			'payer_id'         => $payer_id,
			'covered_id'       => $covered_id,
			'product_id'       => $product->id,
			'product_name'     => $product->name,
			'status'           => $status,
			'payment_term'     => $payment_term,
            'payment_term_new' => $payment_term_new,
			'coverage'         => $coverage,
			'payment_monthly'  => $payment_monthly,
			'payment_annually' => $payment_annually,
            'full_premium'     => $full_premium,
            'payment_without_loading' => $without_loading,
			'deductible'       => 0,
            'corporate_user_status'=>$corporate_user_status,
            'campaign_records'=>$campaign_records,
		]);
    }
}
