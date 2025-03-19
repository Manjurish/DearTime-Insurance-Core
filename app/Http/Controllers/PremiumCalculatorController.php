<?php

namespace App\Http\Controllers;
use App\Helpers;
use App\User;
use App\UserModel;
use App\Individual;
use App\Helpers\Enum;
use App\UwsLoading;
use App\Address;
use App\Product;
use App\Helpers\Helper;

use Illuminate\Http\Request;

class PremiumCalculatorController extends Controller
{
    public function getPrice_campaign(Request $request){

        $user_uuid=$request->input('user_uuid');
        //dd($user_uuid);
        $coverage=$request->input('coverage');
        $product_id=$request->input('product_id');
        $userdetail = User::where('uuid',$user_uuid)->first();
        //$coverage= 100000;
        $product=Product::where('id',$product_id)->first();
        //$product = 'Death';
        $age    = $userdetail->profile->age();
        $gender = $userdetail->profile->gender;
        //$age=40;
       // $gender = 'Male';
		// $uw = $userdetail->underwritings;
        // $answers    =   $uw->answers['answers'] ?? [];
        // $uws_loading_percentage    =   (int) UwsLoading::where('product_id', $product->id)->whereIn('uws_id', $answers)->sum('percentage') ?? 0;

        // todo add ooc_loading
        switch ($product->name) {
            case 'Death' :
                //$occ_loading = $user->occupationJob->death;
                
                $occ_loading = 0;
                $premium_rate = $this->premiumRateAnnually(/*$user,*/ $age, $gender,$product);
                $base_amount =($premium_rate)* $coverage/1000;

                // if ($coverage > 0 && $occ_loading > 0)
                //     $premium_rate += $occ_loading;

                $fprice      =   ($premium_rate * $coverage) / 1000;
                $pPrice    =   $this->premiumRateAnnually(/*$user,*/ $age, $gender,$product) * $coverage/1000;
				// dd($pPrice);
				$dPrice       = $pPrice * ($this->Uwsloadingcampaign(/*$user,*/ $age, $gender,$product) / 100);
				$perPrice = $pPrice + $dPrice;
                return [$perPrice,$base_amount];
                break;

            case 'Disability':
                //$occ_loading = $user->occupationJob->TPD;
               
                $occ_loading = 0;
                $premium_rate = $this->premiumRateAnnually(/*$user,*/ $age, $gender,$product);
                $base_amount =($premium_rate*$coverage/1000);

                    // if ($coverage > 0 && $occ_loading > 0)
                    //     // $premium_rate *= $occ_loading;
    
                    $fprice      =   ($premium_rate * $coverage) / 1000;
					$pPrice    =   $this->premiumRateAnnually(/*$user,*/ $age, $gender,$product) * $coverage / 1000;
					$dPrice       = $pPrice * ($this->Uwsloadingcampaign(/*$user,*/ $age, $gender,$product) / 100);
					$perPrice = $pPrice + $dPrice;
					return [$perPrice,$base_amount];
                    break;

            case 'Accident':
                //$occ_loading = $user->occupationJob->Accident;
                
                $occ_loading = 0;
                $premium_rate = $this->premiumRateAnnually(/*$user,*/ $age, $gender,$product);
                $base_amount =($premium_rate*$coverage/1000);

                    // if ($coverage > 0 && $occ_loading > 0)
                    //     // $premium_rate *= $occ_loading;
    
                    $fprice      =   ($premium_rate * $coverage) / 1000;
                    $pPrice    =   $this->premiumRateAnnually(/*$user,*/ $age, $gender,$product) * $coverage / 1000;
					$dPrice       = $pPrice * ($this->Uwsloadingcampaign(/*$user,*/ $age, $gender,$product) / 100);
					$perPrice = $pPrice + $dPrice; 
                    //return [$perPrice,$base_amount];
                    dd($perPrice,$base_amount);
                    break;
        }
    }
    private function premiumRateAnnually(/*$user,*/ $age, $gender,$product)
    {
        //$age = $u_age ?? $user->age();

        foreach ($product->options()->premium_rates as $pr) {
            if ($pr[0] == $age) {
                if ($gender == Enum::INDIVIDUAL_GENDER_MALE) {
                    return $pr[1];
                } else {
                    return $pr[2];
                }
            }
        }
    }

	private function Uwsloadingcampaign(/*$user,*/ $age, $gender,$product)
		{
			//$age = $u_age ?? $user->age();
	
			foreach ($product->options()->campaign_uw_loading as $pr) {
				if ($pr[0] == $age) {
					if ($gender == Enum::INDIVIDUAL_GENDER_MALE) {
						return $pr[1];
					} else {
						return $pr[2];
					}
				}
			}
		}


}