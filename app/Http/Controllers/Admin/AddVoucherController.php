<?php

namespace App\Http\Controllers\Admin;
use App\Helpers;
use App\User;
use App\UserModel;
use App\Individual;
use App\Helpers\Enum;
use App\UwsLoading;
use App\Address;
use App\Product;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\VoucherCode;

use Illuminate\Http\Request;

class AddVoucherController extends Controller
{   

        public function generate_random_letters($length) {
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            return substr(str_shuffle($letters), 0, $length);
        }

        public function insertvoucher(Request $request){
        

        
        // Static part of the string
        $static_part = "VDT2024";
        
        // Number of strings to generate
        $num_strings = $request->Number_of_records;
        
        // Start number
        $start_number = $request->Number_starts_with;

        $campaign_id = $request->campaign_id;

        $voucher_start_date =$request->valid_from;

        $voucher_end_date =$request->valid_till;
        
        $test=[];
        // Generate and print the strings
        for ($i = 0; $i < $num_strings; $i++) {
            $random_letters = $this->generate_random_letters(3);
            $string_number = str_pad($start_number + $i, 6, '0', STR_PAD_LEFT);
            $validate=VoucherCode::where('id',$start_number + $i)->first();
             // Increment and zero-pad
             if( $validate==null){
            $generated_string = $static_part . $random_letters . $string_number;

            $test[]=$generated_string.$voucher_start_date.$voucher_end_date;

            $addvoucher = new VoucherCode();
            $addvoucher->voucher_code = $generated_string;
            $addvoucher->campaign_id = $campaign_id;
            $addvoucher->valid_from = $voucher_start_date;
            $addvoucher->valid_till = $voucher_end_date;
            $addvoucher->voucher_used = 0;
            $addvoucher->save();
            
             }
             
            // else{
            //     dd('Duplicate scenario success');
            // }
        }

    if(count($test)!=0){
        return redirect()->route('admin.addvoucher.view')->with("success_alert",$num_strings >1 ? $num_strings. "- VoucherCodes Created successfully": $num_strings. "- VoucherCode Created successfully");
          }else{
            return redirect()->route('admin.addvoucher.view')->with("danger_alert","No duplicate records are allowed");
          }


}
}