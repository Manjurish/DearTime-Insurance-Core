<?php     

namespace App\Http\Controllers\Admin;

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

class SarawakController extends Controller
{

public function imp()
	{
		$breadcrumbs = [
			['name' => 'Admin Area','link' => route('admin.dashboard.main')],
			['name' => __('Voucher Campaign Import'),'link' => url()->current()],
		];

		return view('admin.vouchercampaign.index',compact('breadcrumbs'));
	}

	public function importCsv(Request $request)
	{
		$inputFileName = $request->file('file')->getRealPath();
		$reader        = new Csv();
		$spreadsheet   = $reader->load($inputFileName);
		$sheetData     = $spreadsheet->getActiveSheet()->toArray();
		array_shift($sheetData);
		$duplicateRow  = [];
		$addedRow      = 0;

	
		if(!empty($sheetData)){
		    
			foreach ($sheetData as $data) {
                
				$input['voucher_code'] = $data[1];
				$input['nric']   = $data[2];
				$input['age']             = $data[3];
				$input['dob']   = $data[4];
				$input['gender']          = $data[5];
				$input['name']      = $data[6];
				$input['email']          = $data[7];
				$input['mobile']      = $data[8];
				$input['nationality']          = $data[9];
				$input['residential_address']      = $data[10];
				$input['state']          = $data[11];
				$input['city']      = $data[12];
				$input['zipcode']          = $data[13];
				$input['country']      = $data[14];
				$input['other_life_insurance']          = $data[15];
				$input['declaration']      = $data[16];
				$input['existing_user']          = $data[17];
				$input['premium_annually_death']      = $data[21];
				$input['premium_annually_accident']          = 0;
				$input['premium_annually_disability']      = 0;
				$input['full_premium_death']          =$data[22] ;
				$input['full_premium_accident']      = 0;
				$input['full_premium_disability']          = 0;
				$input['payment_without_loading_death']      = $data[23];
				$input['payment_without_loading_accident']      = 0;
				$input['payment_without_loading_disability']          = 0;
				$input['first_payment_on']      = $data[24];
				$input['next_payment_on']          = $data[25];
				$input['invoice_no']      = $data[26];
				$input['transaction_id']          = $data[27];
				$input['amount']      = $data[28];
				$input['payment_date']      = $data[29];
				$input['coverage_death']          = $data[20];
				$input['coverage_accident']      = 0;
				$input['coverage_disability']          = 0;
				$input['gateway']      = $data[30];
				$input['card_type']      = $data[31];
			
				 $checknric = VoucherCampaignUpload::where('nric',$input['nric'])->count();
                
										
				if($checknric == 0){
					VoucherCampaignUpload::create($input);
				 	$addedRow++;
				 	}							
               // dd($input);
		 }

        

		}

		//$upload_consider=VoucherCampaignUpload::where('uploaded','!=',1)->get()->pluck('voucher_code')->toArray();

		$testusers = VoucherCampaignUpload::where('uploaded','!=',1)->get();


            
        foreach($testusers as $testuser){
        //try { 
            $email = $testuser->email;

            $existemail = UserModel::where('email',$testuser->email)->first() ?? null;

            $mobile = $testuser->mobile;

            $existmobile = Individual::where('mobile',$testuser->mobile)->first() ?? null;

            $nric = $testuser->nric;

            $existnric = Individual::where('nric',$testuser->nric)->first() ?? null;
           
            //dd($testuser->voucher_code);

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
                                $coverage = $testuser->coverage_death;
                                $payment_annually = 0;
                                $full_premium = 0;
                                $payment_monthly = 0;
                                $without_loading = 0;

                                $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading);
                            
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
            $address->city = City::where('name',$testuser->city)->first()->uuid;
            $address->postcode = PostalCode::where('name',$testuser->zipcode)->first()->uuid;
            $address->state = State::where('name',$testuser->state)->first()->uuid;
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
                $coverage = $testuser->coverage_death;
                $payment_annually = 0;
                $full_premium = 0;
                $payment_monthly = 0;
                $without_loading = 0;

                $newCoverage = $this->createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading);
            
        } 

    }
    

    }

	// }catch (\Exception $e) {
	// 	echo 'Exception:';
	// 	dd($e->getMessage());
	// }
}

		//try { 


		$datas = VoucherCampaignUpload::where('uploaded','!=',1)->get();
		
		foreach ($datas->groupBy('transaction_id') as $data ){

				$voucher_cam =VoucherCode::where('voucher_code',$data[0]->voucher_code)->first()->campaign_id;

				$voucher_ca = VoucherCampaign::where('id',$voucher_cam)->first()->campaign_email;
	
				$payer_id = UserModel::where('email',$voucher_ca)->first()->id;
			// dd($data);
				$new_order = new Order();
				$new_order->payer_id = $payer_id;
				$new_order->amount = $data[0]->amount;
				$new_order->true_amount = $data[0]->amount;
				$new_order->status = Enum::ORDER_SUCCESSFUL;
				$new_order->type = Enum::ORDER_TYPE_NEW;
				$new_order->grace_period = 30;
				$new_order->due_date = now();
				$new_order->last_try_on = now();
				$new_order->next_try_on = Carbon::today()->addDays(7);
				$new_order->retries = 5;		
			    $new_order->save();
				// dd($new_order);

				
	

				$transaction = new Transaction();
				$transaction->order_id = $new_order->id;
				$transaction->success = 1;
				$transaction->date = $data[0]->payment_date;
				$transaction->gateway = 'campaign';
				$transaction->transaction_ref = 'TRX'.time();
				$transaction->amount = $new_order->amount;
				$transaction->card_no = '12345678';
				$transaction->card_type = $data[0]->card_type;
				$transaction->transaction_id = $data[0]->transaction_id;
				//  dd($transaction);
				$transaction->save();

		}

		// dd($datas);
		foreach($datas as $da){

			$voucher_cam =VoucherCode::where('voucher_code',$da->voucher_code)->first()->campaign_id;

			$voucher_ca = VoucherCampaign::where('id',$voucher_cam)->first()->campaign_email;
	
			$payer_id = UserModel::where('email',$voucher_ca)->first()->id;
			
            $nric = $da->nric;

			$checkn = Individual::where('nric',$nric)->first();


			$covs = Coverage::where('owner_id',$checkn->id)->where('payer_id',$payer_id)->whereIn('status',[Enum::COVERAGE_STATUS_UNPAID,Enum::COVERAGE_STATUS_INCREASE_UNPAID])->get();

			foreach($covs as $cov){
              
				$products = Product::all();

				foreach ($products as $product){

					if($product->id == 1){
					 
					$covv = Coverage::where('owner_id',$checkn->id)->where('payer_id',$payer_id)->where('product_name','Death')->where('status','increase-unpaid')->first() ?? null;

					$covv_act = Coverage::where('owner_id',$checkn->id)->where('product_name','Death')->where('status','active')->first() ?? null;



                    if($covv != null){

						$covv->update(
									[
										'status' => 'active-increased',
									    'state'  =>'active',
										'last_payment_on' => $da->payment_date,
										'first_payment_on' => $covv_act->first_payment_on,
										'next_payment_on' => $covv_act->next_payment_on,
										'renewal_date' =>$covv_act->renewal_date,
										'ndd_payment_due_date'=>$covv_act->next_payment_on,
										'payment_annually' =>$da->premium_annually_death,
										'payment_monthly' =>Helpers::round_up($da->premium_annually_death * 0.085, 2),
										'full_premium'  =>$da->premium_annually_death,
										'payment_without_loading' => $da->premium_annually_death,
										'has_loading' => 1,
										'payor_first_product_purchase_date' => $covv_act->first_payment_on,
										'payor_next_payment_date' => $covv_act->next_payment_on,
										'payor_last_payment_on' => $da->payment_date,
										'csd_corporate_invoice_date' => $da->payment_date,
										'parent_id' => $covv_act->id

		
									]
									);

					 }else{

						// dd($covv);
						$cov_a = Coverage::where('owner_id',$checkn->id)->where('payer_id',$payer_id)->where('product_name','Death')->where('status','unpaid')->first() ?? null;

                        if($cov_a != null){
						
						$cov_a->update(
							[
										'status' => 'active',
										'state'  =>'active',
										'last_payment_on' => $da->first_payment_on,
										'first_payment_on' => $da->first_payment_on,
										'next_payment_on' => $da->next_payment_on,
										'renewal_date' =>$da->next_payment_on,
										'ndd_payment_due_date'=>$da->next_payment_on,
										'payment_annually' =>$da->premium_annually_death,
										'payment_monthly' =>Helpers::round_up($da->premium_annually_death * 0.085, 2),
										'full_premium'  =>$da->premium_annually_death,
										'payment_without_loading' => $da->premium_annually_death,
										'has_loading' => 1,
										'payor_first_product_purchase_date' => $da->first_payment_on,
										'payor_next_payment_date' => $da->next_payment_on,
										'payor_last_payment_on' => $da->first_payment_on,
										'csd_corporate_invoice_date' => $da->first_payment_on

							]
							);

					 }
					}

					//  dd($covv_a);

			}

		}
	}

	foreach($covs as $cov){

		$c_o = new CoverageOrder();
		$c_o->coverage_id = $cov->id;
		$c_o->order_id = $new_order->id;
		// dd($c_o);
		$c_o->save();

	}

	$thanks = Thanksgiving::where('individual_id',$checkn->id)->latest()->first();
	if(empty($thanks)){
		$thanks = new Thanksgiving();
		$thanks->individual_id = $checkn->id;
		$thanks->type = 'charity';
		$thanks->percentage = 100;
		$thanks->save();
	}
	

   
	foreach($covs as $cov){
		$c_t = new CoverageThanksgiving();
		$c_t->coverage_id = $cov->id;
		$c_t->thanksgiving_id = $thanks->id;
		// dd($c_o);
		$c_t->save();

	}

	// $uw = Underwriting::where('individual_id',$checkn->id)->latest()->first();
    
	// if(empty($uw)){
   	    $uw = new Underwriting();
		$uw->individual_id = $checkn->id;
    	$answers = json_encode(['weight' => 70, 'height' => 175, 'smoke' => 0, 'answers' => [34, 53, 39, 52, 57, 59, 61]]);
		$uw->answers = json_decode($answers, true);
		$uw->death = 1;
		$uw->disability = 1;
		$uw->ci = 1;
		$uw->medical = 1;
		$uw->created_by = $checkn->user_id;
		$uw->sio_answers = json_encode(['weight' => 70, 'height' => 175, 'smoke' => 0, 'answers' => [34, 53, 39, 52, 57, 59, 61]]);
		$uw->save();
	// }
	

	foreach($covs as $cov){
	$cov->update([
		'uw_id'=>$uw->id
	]);
 }

   
 $sum_cov = Coverage::where('owner_id',$checkn->id)->where('payer_id',$payer_id)->where('state','active')->get()->sum('payment_annually');

//  dd($sum_cov);


 $thank_self = Thanksgiving::where('individual_id',$checkn->id)->where('type','self')->latest()->first();

 $thank_charity = Thanksgiving::where('individual_id',$checkn->id)->where('type','charity')->latest()->first();

 if($thank_charity){
	Credit::create([
		'order_id'=>$new_order->id,
		'from_id'=>$checkn->user_id,
		'amount'=>$sum_cov * ($thank_charity->percentage / config('static.thanksgiving_percent')),
		'type'=>Enum::CREDIT_TYPE_THANKS_GIVING,
		'type_item_id'=> $thank_charity->id,
	]);

	if($thank_charity){
		$sop_fund= new SpoCharityFunds;
		$sop_fund->user_id =$checkn->user_id;
		$sop_fund->order_id=$new_order->id;
		$sop_fund->transaction_id= $new_order->transactions()->latest()->first()->id;
		$sop_fund->transactions_no=$new_order->transactions()->latest()->first()->transactions_ref;
		$sop_fund->amount =$sum_cov;
		$sop_fund->percentage =$thank_charity->percentage;
		$sop_fund->charity_fund=$sum_cov * ($thank_charity->percentage / config('static.thanksgiving_percent'));
		if($checkn->freelook()){
		$sop_fund->status ='ON HOLD';
		}else{
		$sop_fund->status ='ADDED';
		}
		$sop_fund->save();
		}

 }

 if($thank_self){
	Credit::create([
		'order_id'=>$new_order->id,
		'from_id'=>$checkn->user_id,
		'amount'=>$sum_cov * ($thank_self->percentage / config('static.thanksgiving_percent')),
		'type'=>Enum::CREDIT_TYPE_THANKS_GIVING,
		'type_item_id'=> $thank_charity->id,
	]);

	Credit::create([
		'order_id'=>$new_order->id,
		'from_id'=>$checkn->user_id,
		'amount'=>-1*($sum_cov * ($thank_self->percentage / config('static.thanksgiving_percent'))),
		'type'=>Enum::CREDIT_TYPE_THANKS_GIVING,
		'type_item_id'=> $thank_charity->id,
	]);
 }

 $actions =[];
 //  (['new_death' => 20000, 'new_accident' => 20000, 'new_disability' => 100000, 'new_payment_term'=> 'annually']);
 $actions['new_death'] = $da->coverage_death;
 $actions['new_payment_term'] = 'annually';

	$new_action = new Action();
	$new_action->user_id = $checkn->user_id;
	$new_action->type = 'Member Addition';
	$new_action->event = 'newMember';
	$new_action->actions = $actions;
	$new_action->status = 'executed';
	$new_action->execute_on = now();
	$new_action->createdbyable_type = 'User';
	$new_action->createdbyable_id = $checkn->user_id;
	$new_action->save();


	$cov_id = [];

	foreach ($covs as $cov) {
		$cov_id[] = $cov->id;
	}
	
	$new_action->coverages()->attach($cov_id);

	$da->uploaded=1;
	$da->save();

 

		
}

		return redirect()->back()->with(['duplicateRow' => array_unique($duplicateRow),'addedRow' => $addedRow]);

	// }catch (\Exception $e) {
	// 	echo 'Exception:';
	// 	dd($e->getMessage());
	// }

	}

private function createCoverage($payment_term,$payment_term_new,$owner_id,$payer_id,$covered_id,$product,$status,$coverage,$payment_monthly,$payment_annually,$full_premium,$without_loading)
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
            'corporate_user_status'=>'accepted',
            'campaign_records'=> 1
		]);
    }

}

