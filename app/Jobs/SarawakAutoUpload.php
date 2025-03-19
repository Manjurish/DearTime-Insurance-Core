<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\VoucherDetails;
use App\VoucherCode;
use Carbon\Carbon;
use App\UserModel;
use App\Individual;
use App\VoucherCampaign;
use App\Product;
use App\Coverage;
use App\User;
use App\Address;
use App\Thanksgiving;
use App\Underwriting;
use App\Helpers\Enum;
use Illuminate\Http\Request;

class SarawakAutoUpload implements ShouldQueue
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

        $testuser=VoucherDetails::where('created','!=',1)->whereDate('created_at','=',Carbon::today())->get()->filter(function ($item){
            $check=VoucherCode::where('voucher_code',$item->voucher_code)->first();
            if($check->campaign_id == 2){
            return $item;
        }
        });

        if($testuser!=null){
            foreach($testuser as $tu){

            $email=$tu->email;

            $checkemail=UserModel::where('email', $email)->first();



            $nric = $tu->nric;

            $checknric = Individual::where('nric', $nric)->first();



            $mobile= $tu->mobile;

            $checkmobile = Individual::where('mobile', $mobile)->first();


            $Vouchercheck=VoucherCode::where('voucher_code',$tu->voucher_code)->first()->campaign_id;

            $Campaign_check=VoucherCampaign::where('id', $Vouchercheck)->first()->campaign_email;

            $payer_id=Usermodel::where('email', $Campaign_check)->first()->id;

            if($checknric!=null && $checkmobile!=null && $checkemail!=null){

    

                $products=Product::all();

                foreach($products as $product){

                    if($product->id == 1){

                        $coveragecheck=Coverage::where('owner_id',$checknric->id)->wherein('status',[Enum::COVERAGE_STATUS_ACTIVE,Enum::COVERAGE_STATUS_ACTIVE_INCREASED])->where('product_id',$product->id)->get();

                        if (($coveragecheck)->isNotEmpty()){
                        $status='increase-unpaid';
                        }else{
                        $status='unpaid';  
                        }

                        if($tu->age>=20 && $tu->age<=40){
                            $coverage='5000';
                        }elseif($tu->age>=40 && $tu->age<=60){
                            $coverage='2500';
                        }

                        $create=$this->createcoverage($checknric->id, $payer_id,$product->id,$product->name,$status,$coverage);

                    }

                    
                }
                
                $thanksgivingcheck=Thanksgiving::where('individual_id',$checknric->id)->get();
                    if(!($thanksgivingcheck)->isNotEmpty()){
                        $addthanksgivingex=new Thanksgiving();
                        $addthanksgivingex->individual_id=$checknric->id;
                        $addthanksgivingex->type='charity';
                        $addthanksgivingex->percentage=100;
                        $addthanksgivingex->save();
                    }


            }elseif($checknric==null && $checkmobile==null && $checkemail==null){



                $products=product::all();

                foreach($products as $product){

                if($product->id== 1){

                if($tu->age>=20 && $tu->age<=40){
                    $coverage='5000';
                }else{
                    $coverage='2500';
                }

                $status='unpaid';

                $adduser=new user();
                $adduser->type='individual';
                $adduser->email=$tu->email;
                $adduser->password='';
                $adduser->active='1';
                $adduser->locale='en';
                $adduser->save();

                $addaddress=new Address();
                $addaddress->address1=$tu->residential_address;
                $addaddress->city=$tu->city;
                $addaddress->postcode=$tu->zipcode;
                $addaddress->state=$tu->state;
                $addaddress->country=$tu->country;
                $addaddress->save();

                $addindividual=new Individual();
                $addindividual->user_id=$adduser->id;
                $addindividual->name=$tu->name;
                $addindividual->nric=$tu->nric;
                $addindividual->nationality=$tu->nationality;
                $addindividual->country_id='135';
                $addindividual->dob=$tu->dob;
                $addindividual->gender=$tu->gender;
                $addindividual->mobile=$tu->mobile;
                if($tu->other_life_insurance=='Yes'){
                $addindividual->has_other_life_insurance='1'; 
                }else{
                    $addindividual->has_other_life_insurance='0'; 
                }
                $addindividual->address_id=$addaddress->id;
                $addindividual->save();


                $create=$this->createcoverage($addindividual->id, $payer_id,$product->id,$product->name,$status,$coverage);

                $addthanksgiving=new Thanksgiving();
                $addthanksgiving->individual_id=$addindividual->id;
                $addthanksgiving->type='charity';
                $addthanksgiving->percentage='100';
                $addthanksgiving->save();
            }

            }

        }


        // $adduw= new Underwriting();
        // $individual_id=Individual::where('nric',$tu->nric)->first();
        // $adduw->individual_id=$individual_id->id;
        // $adduw->death='1';
        // $adduw->disability='1';
        // $adduw->ci='1';
        // $adduw->medical='1';
        // $adduw->created_by=$individual_id->user_id;
        // $adduw->sio_answers=json_encode(['title'=>'Health Declaration','declaration'=>'Medical Survey is good and healthy.']);
        // $uwanswers=json_encode(['title'=>'Health Declaration','declaration'=>'Medical Survey is good and healthy.']);
        // $adduw->answers=json_decode($uwanswers,true);
        // $adduw->save();

        $adduw= new Underwriting();
        $individual_id=Individual::where('nric',$tu->nric)->first();
        $adduw->individual_id=$individual_id->id;
        $adduw->death='1';
        $adduw->disability='1';
        $adduw->ci='1';
        $adduw->medical='1';
        $adduw->created_by=$individual_id->user_id;
        $adduw->sio_answers=json_encode(['weight' => 70, 'height' => 175, 'smoke' => 0, 'answers' => [34, 53, 39, 52, 57, 59, 61]]);
        $uwanswers=json_encode(['weight' => 70, 'height' => 175, 'smoke' => 0, 'answers' => [34, 53, 39, 52, 57, 59, 61]]);
        $adduw->answers=json_decode($uwanswers,true);
        $adduw->save();

        $tu->created=1;
        $tu->save();


       // $update=VoucherDetails::where('voucher_code',$tu->voucher_code)->update('created',1);
    }
        }
    }



public function createcoverage($owner_id,$payer_id,$product_id,$product_name,$status,$coverage){

    $add=new Coverage();
    $add->owner_id = $owner_id;
    $add->payer_id = $payer_id;
    $add->covered_id = $owner_id;
    $add->product_id = $product_id;
    $add->product_name = $product_name;
    $add->status = $status;
    $add->state= 'inactive';
    $add->payment_term ='annually';
    $add->payment_term_new ='annually';
    $add->coverage = $coverage;
    $add->corporate_user_status ='accepted';
    $add->campaign_records='1';
    $add->payment_without_loading='0';
    $add->full_premium='0';
    $add->deductible='0';
    $add->has_loading='1';
    $add->save();

}


}
