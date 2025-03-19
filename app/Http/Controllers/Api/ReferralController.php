<?php     

namespace App\Http\Controllers\Api;

use App\Referral;
use App\ReferralCode;
use App\User;
use App\Individual;
use App\Thanksgiving;
use Carbon\Carbon;
use App\Helpers;
use App\Helpers\NextPage;
use App\Notifications\EmailPromoter;
use App\IndustryJob;
use App\Credit;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use DB;
use View;
use Mpdf\Mpdf;
use PDF;
use DataTables;
use Illuminate\Support\Str;



class ReferralController extends Controller
{
    public function countref(Request $request)
    {

        $user=auth()->user();
        $count = Referral::where('from_referrer',$user->id)->groupBy('to_referee')->get()->count();
        $amount = Referral::where('from_referrer',$user->id)->where('payment_status','PAID')->sum('amount');

        return ['status' => 'success', 'data' => [
            'count' => $count,
            'amount' => round($amount,2),
         ]];
    }


    function generateCode(Request $request)
        {
            $length = 10;
            $data = ['1','2','3','4','5','6','7','8','9','0','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
            $res = '';
            for ($i=0;$i<$length;$i++)
            {
                $res .= $data[rand(0,count($data)-1)];
            }

            $user=auth()->user();
            $count = Referral::where('from_referrer',$user->id)->count();
            $amount = Referral::where('from_referrer',$user->id)->sum('amount');
            // $skipref = !$user->needReferral();
            $referralcode = new Referralcode;
            $refexist = false;
            $refcheck = Referralcode::where('referralcode',$res)->first();
            $referralcode->referralcode = $res;
            $referralcode->individual_id = $request->user()->profile->id;
            // $inref = Individual::where('$referralcode->referralcode',$res)->update();
          if($refcheck){
            $refexist = true;
            // $skipref = true;
             return ['status' => 'error', 'message' => 'Already exist ref code'];
          }
            $indexist = false;
            $indcheck =Referralcode::where('individual_id',$request->user()->profile->id)->first();
            if($indcheck){
                $indexist = true;
                // $skipref = true;
                 return ['status' => 'error', 'message' => 'Already exist individual id'];
            }
            $referralcode->save();
            Individual::where('id',$request->user()->profile->id)->update(['referral_code' => $res]);

            return ['status' => 'success','data' => [
            'count' => $count,
            'amount' => $amount,
            'referral_code'   => $res,
            ]
            ];
        }

        public function promoters(Request $request)
        {
            $user = $request->user();
            if(empty($user))
                $user = auth()->user();
    
            $pro = Referral::where('from_referrer',$user->id)->groupBy('to_referee')->get();   

            $promotedArray = [];
            foreach ($pro as $promoted){
                $uss = User::where('id',$promoted->to_referee)->first();
                $ind = Individual::where('user_id',$promoted->to_referee)->first();
                $thanksgiving=Thanksgiving::whereType('promoter')->where('individual_id',$ind->id)->latest()->first();
                array_push($promotedArray,[
                    'uuid'=>$uss->uuid,
                    'register_on'=>Carbon::parse($uss->created_at)->format(config('static.date_format')),
                    'name'=>$ind->name,
                    'thanksgiving'=>$thanksgiving,
                ]);
            }
    
            return ['status' => 'success', 'data' => [
                'promoted'=>$promotedArray,
            ]];
        }
    

      public function monthly(Request $request) 
      {
        $user=auth()->user();

        $locale = $user->locale;

        $data2 = $request->input('month');

        $name = $user->name;

        $account = $user->profile->bankAccounts()->latest()->first()->account_no;
        
        $data1 = $request->input('year') ?? date("Y");
        if($locale == 'en'){
        $y = Referral::where('from_referrer',$user->id)->where('month',$data2)->where('year',$data1)->where('payment_status','PAID')->get();
        }
        if($locale == 'bm'){
          $y = Referral::where('from_referrer',$user->id)->where('month_bm',$data2)->where('year',$data1)->where('payment_status','PAID')->get();
          }

        if($locale == 'ch'){
            $y = Referral::where('from_referrer',$user->id)->where('month_ch',$data2)->where('year',$data1)->where('payment_status','PAID')->get();
            }

        if($locale == 'en'){
         foreach ($y->groupBy('to_referee') as $ys ){
            $data[] = ['month'=> $ys[0]->month,'amount'=>$ys->sum('amount') ,'name'=> $ys[0]->to_referee_name,'transaction_ref'=>$ys[0]->transaction_ref,'transaction_date'=>Carbon::parse($ys[0]->transaction_date)->format('d/m/Y')];
        }
      }

        if($locale == 'bm'){
        foreach ($y->groupBy('to_referee') as $ys ){
           $data[] = ['month'=> $ys[0]->month_bm,'amount'=>$ys->sum('amount') ,'name'=> $ys[0]->to_referee_name,'transaction_ref'=>$ys[0]->transaction_ref,'transaction_date'=>Carbon::parse($ys[0]->transaction_date)->format('d/m/Y')];
       }
     }

       if($locale == 'ch'){
        foreach ($y->groupBy('to_referee') as $ys ){
         $data[] = ['month'=> $ys[0]->month,'amount'=>$ys->sum('amount') ,'name'=> $ys[0]->to_referee_name,'transaction_ref'=>$ys[0]->transaction_ref,'transaction_date'=>Carbon::parse($ys[0]->transaction_date)->format('d/m/Y')];
    }
   }

   if($locale == 'en'){
    $amounts = Referral::where('from_referrer',$user->id)->where('month',$data2)->where('year',$data1)->where('payment_status','PAID')->sum('amount');
    }

   if($locale == 'bm'){
    $amounts = Referral::where('from_referrer',$user->id)->where('month_bm',$data2)->where('year',$data1)->where('payment_status','PAID')->sum('amount');
   }   
   
    if($locale == 'ch'){
      $amounts = Referral::where('from_referrer',$user->id)->where('month_ch',$data2)->where('year',$data1)->where('payment_status','PAID')->sum('amount');
     }

        return ['status' => 'success', 'data' => [
             'name_trans' =>__('mobile.dear',['name' => ($user->name)]),
             'bankaccount' => $account,
             'total' => $amounts,
             'data'=>$data ?? null,
        ]];


      }

      public function yearly(Request $request) 
      {
        $user=auth()->user();

        $locale = $user->locale;
        
        $data1 = $request->input('year') ?? date("Y");

        $y = Referral::where('from_referrer',$user->id)->where('year',$data1)->where('payment_status','PAID')->get();

        if($locale == 'en'){
        foreach ($y->groupBy('month') as $ys ){
            $data[] = ['month'=> $ys[0]->month,'amount'=>$ys->sum('amount'),'year'=>$ys[0]->year];
        }
      }

      if($locale == 'bm'){
        foreach ($y->groupBy('month') as $ys ){
            $data[] = ['month'=> $ys[0]->month_bm,'amount'=>$ys->sum('amount'),'year'=>$ys[0]->year];
        }
      }

      if($locale == 'ch'){
        foreach ($y->groupBy('month') as $ys ){
            $data[] = ['month'=> $ys[0]->month_ch,'amount'=>$ys->sum('amount'),'year'=>$ys[0]->year];
        }
      }

        $ye = Referral::where('from_referrer',$user->id)->where('payment_status','PAID')->get();

        foreach ($ye->groupBy('year') as $yes ){
            $list_of_year[] = ['year'=> $yes[0]->year];
        }

        $amount = Referral::where('from_referrer',$user->id)->where('year',$data1)->where('payment_status','PAID')->sum('amount');
        
        if (empty($list_of_year) || empty($data)){
             return ['status' => 'error'];
        }else{
            return ['status' => 'success', 'data' => [
            'year'   => $data1,
            'list_of_year' => $list_of_year,
            'total_amount' =>$amount,
            'annual_stat_des' =>__('mobile.annual_stat_des',['yt' => $data1]),
            'name_trans' =>__('mobile.dear',['name' => ($user->name)]),
            'data' =>$data,
            'year_trans' =>__('mobile.year_trans',['yeartrans' => ($data1)]),

        ]];
        }
    }

    public function termscondition(Request $request) 
      {
        
        $user=auth()->user();

        $locale = $user->locale;

        if($locale == 'en'){

            $data = 'https://dt-insurance-dtdevtest-bucket.s3.ap-southeast-1.amazonaws.com/Referral_Termsandcondition/Terms_and_Conditions.pdf';

        }
        
        if($locale == 'bm'){

          $data = 'https://dt-insurance-dtdevtest-bucket.s3.ap-southeast-1.amazonaws.com/Referral_Termsandcondition/Terms_and_Conditions_BM.pdf';

      }

      if($locale == 'ch'){

        $data = 'https://dt-insurance-dtdevtest-bucket.s3.ap-southeast-1.amazonaws.com/Referral_Termsandcondition/Terms_and_Conditions_CH.pdf';

    }
        
           
            return ['status' => 'success', 'data' => [

                'link' => $data,
                'type' => 'pdf',
                'title' => 'Terms and Conditions'

            ]];
      }


    public function yearlypdf(Request $request) 
      {
        $user=auth()->user();

        $locale = $user->locale;
        
        $data1 = $request->input('year') ?? date("Y");

        $y = Referral::where('from_referrer',$user->id)->where('year',$data1)->where('payment_status','PAID')->get();

        foreach ($y->groupBy('month') as $ys ){
            $data_1[] = ['month'=> $ys[0]->month,'amount'=>$ys->sum('amount')];
        }

        $ye = Referral::where('from_referrer',$user->id)->where('payment_status','PAID')->get();

        foreach ($ye->groupBy('year') as $yes ){
            $list_of_year[] = ['year'=> $yes[0]->year];
        }

        $amount = Referral::where('from_referrer',$user->id)->where('year',$data1)->where('payment_status','PAID')->sum('amount');
        
        $name = $user->profile->name;

        $year = $request->input('year') ?? date("Y");

        $months1 = json_decode('[{"month":"December","amount":"0"},{"month":"November","amount":"0"},{"month":"October","amount":"0"},{"month":"September","amount":"0"},{"month":"August","amount":"0"},{"month":"July","amount":"0"},{"month":"June","amount":"0"},{"month":"May","amount":"0"},{"month":"April","amount":"0"},{"month":"March","amount":"0"},{"month":"February","amount":"0"},{"month":"January","amount":"0"}]', true);
        $json_string = json_encode($data_1);
        $months2 = json_decode($json_string, true);
        
        foreach ($months2 as $m2) {
          foreach ($months1 as &$m1) {
            if ($m1['month'] === $m2['month']) {
              $m1['amount'] = $m2['amount'];
            }
          }
        }

      $ms = [];

        foreach($months1 as $ms1){
          $ms [$ms1['month']] = $ms1['amount'];
        }
        
        $data = [
            'name' => $name,
            'year' => $year,
            'amount' =>$amount,
            'data'  => $ms,
        ];

        if($locale == 'bm'){
      
          $payload['data'] = $data;
          $payload['filename'] = 'YearlyStatementBM.docx';

        }
    
         if($locale == 'en'){
         
          $payload['data'] = $data;
          $payload['filename'] = 'YearlyStatementEN.docx';

         }
  
         if($locale == 'ch'){
        
        $payload['data'] = $data;
        $payload['filename'] = 'YearlyStatementCH.docx';
    
         }

        try {
          $response = Http::asJson()->retry(3, 5)->post(env('FACE_API_URL_REF'),$payload);
          Log::info(env('FACE_API_URL'));
          Log::info($payload);
          Log::info($response);
          $file = $response->body();
          //dd($file);
      }catch (\Exception $e){
           Log::error($e->getMessage());
           dd($e->getMessage());
      }
      return Response::make($file, 200, ['Content-Type' => 'application/pdf']);
  
       }
  

    public function monthlypdf(Request $request) 
     {
       $user=auth()->user();

       $locale = $user->locale;

       $data2 = $request->input('month');

       $account = $user->profile->bankAccounts()->latest()->first()->account_no;
      
       $data1 = $request->input('year');

     if($locale == 'en'){
       $y = Referral::where('from_referrer',$user->id)->where('year',$data1)->where('month',$data2)->where('payment_status','PAID')->get();
       }

    if($locale == 'bm'){
        $y = Referral::where('from_referrer',$user->id)->where('year',$data1)->where('month_bm',$data2)->where('payment_status','PAID')->get();
        }

    if($locale == 'ch'){
        $y = Referral::where('from_referrer',$user->id)->where('year',$data1)->where('month_ch',$data2)->where('payment_status','PAID')->get();
          }

       $trans_ref = $y->where('transaction_ref')->first()->transaction_ref;

       $trans = $y->where('transaction_date')->first()->transaction_date;

       $trans_date = Carbon::parse($trans)->format('d/m/Y');
    
     if ($locale == 'en'){
     foreach ($y->groupBy('to_referee') as $ys ){
        $data_3[] = ['month'=> $ys[0]->month,'amount'=>$ys->sum('amount') ,'name'=> $ys[0]->to_referee_name,'transaction_ref'=>$ys[0]->transaction_ref,'transaction_date'=>Carbon::parse($ys[0]->transaction_date)->format('d/m/Y')];
     }
    }

     if ($locale == 'bm'){
      foreach ($y->groupBy('to_referee') as $ys ){
         $data_3[] = ['month_bm'=> $ys[0]->month_bm,'amount'=>$ys->sum('amount') ,'name'=> $ys[0]->to_referee_name,'transaction_ref'=>$ys[0]->transaction_ref,'transaction_date'=>Carbon::parse($ys[0]->transaction_date)->format('d/m/Y')];
      }
     }

     if ($locale == 'ch'){
      foreach ($y->groupBy('to_referee') as $ys ){
         $data_3[] = ['month_ch'=> $ys[0]->month_ch,'amount'=>$ys->sum('amount') ,'name'=> $ys[0]->to_referee_name,'transaction_ref'=>$ys[0]->transaction_ref,'transaction_date'=>Carbon::parse($ys[0]->transaction_date)->format('d/m/Y')];
      }
     }
     
     if($locale == 'en'){
      $amounts = Referral::where('from_referrer',$user->id)->where('month',$data2)->where('year',$data1)->where('payment_status','PAID')->sum('amount');
     }

     if($locale == 'bm'){
      $amounts = Referral::where('from_referrer',$user->id)->where('month_bm',$data2)->where('year',$data1)->where('payment_status','PAID')->sum('amount');
     }

     if($locale == 'ch'){
      $amounts = Referral::where('from_referrer',$user->id)->where('month_ch',$data2)->where('year',$data1)->where('payment_status','PAID')->sum('amount');
     }

      $name = $user->profile->name;

     $data = [
        'name' => $name,
        'account' => $account,
        'amounts'  => $amounts,
        'data_3'  => $data_3,
        'trans_ref' => $trans_ref,
        'trans_date' => $trans_date,
      ];
     
      if($locale == 'bm'){
      
        $payload['data'] = $data;
        $payload['filename'] = 'MonthlyStatementBM.docx';

      }
  
      if($locale == 'en'){
        
        $payload['data'] = $data;
        $payload['filename'] = 'MonthlyStatementEN.docx';

      }

      if($locale == 'ch'){
        
        $payload['data'] = $data;
        $payload['filename'] = 'MonthlyStatementCH.docx';
  
      }


    try {
      $response = Http::asJson()->retry(3, 5)->post(env('FACE_API_URL_REF'),$payload);
      Log::info(env('FACE_API_URL'));
      Log::info($payload);
      Log::info($response);
      $file = $response->body();
      //dd($file);
  }catch (\Exception $e){
       Log::error($e->getMessage());
       dd($e->getMessage());
  }
  return Response::make($file, 200, ['Content-Type' => 'application/pdf']);

}
}
