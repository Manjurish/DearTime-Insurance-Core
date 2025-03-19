<?php     

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Refund;

#use App\BankTemp;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
class RefundController extends Controller
{
	public function index()
	{
		/*$result=array();
		$refund_details = Refund::whereNotNull('id')->get();

		if($refund_details)
		{	BankTemp::truncate($result);
			foreach($refund_details as $refund_value)
			{
				foreach($refund_value->receiver->profile->bankAccounts as $re_key=>$re_value)
				{
					
					$result[] = ['refund_id'=>$refund_value->id,'account_no'=>$re_value->account_no,'bank_name'=>$re_value->bank_name,
								'owner_id'=>$re_value->owner_id,'owner_type'=>$re_value->owner_type,
								'uuid'=>$re_value->uuid
								];
				}
			}
			BankTemp::insert($result);
			//echo "<pre>";print_r($result);
			//die;

		}*/
		
		//DB::table('bank_temps')->dropIfExists();
		//Schema::drop('bank_temps');

		$breadcrumbs = [
			['name' => 'Admin Area','link' => route('admin.dashboard.main')],
			['name' => 'Refunds','link' => url()->current()],
		];
		//Artisan::call("migrate", ["--path" => "/database/migrations/2022_06_08_182114_create_bank_temps_table.php"]);
		Schema::create('bank_temps', function (Blueprint $table) {
         
            $table->id();
            $table->integer('refund_id');
            $table->uuid('uuid');
            $table->integer('owner_id'); // for both individual & group
            $table->string('owner_type');
            $table->text('account_no');
            $table->text('bank_name');
            $table->timestamps();
            $table->temporary();
        });


        
            $result=array();
            $refund_details = Refund::whereNotNull('id')->get();
    
            if($refund_details)
            {	
				foreach($refund_details as $refund_value)
                {
					if(!$refund_value->receiver->isCorporatePayer())
                {
                    foreach($refund_value->receiver->profile->bankAccounts as $re_key=>$re_value)
                    {
                        $account_no = $refund_value->corporate_type =='payorcorporate'?'':$re_value->account_no;
						$bank_name =$refund_value->corporate_type =='payorcorporate'?'':$refund_value->receiver->profile->bankAccounts[0]->bank_name;

                        $result[] = ['refund_id'=>$refund_value->id,'account_no'=>$account_no,'bank_name'=>$bank_name ,
                                    'owner_id'=>$re_value->owner_id,'owner_type'=>$re_value->owner_type,
                                    'uuid'=>$re_value->uuid
                                    ];
                    }
				}
				else 
				{
					$result[] = ['refund_id'=>$refund_value->id,'account_no'=>'','bank_name'=>'' ,
                                    'owner_id'=>$re_value->owner_id,'owner_type'=>$re_value->owner_type,
                                    'uuid'=>$re_value->uuid
                                    ];
				}
                }
                DB::table('bank_temps')->insert($result);

                //DB::table('bank_temps')->insert($result);
              //  $a = DB::select('select * from bank_temps'); dd($a);
               // echo "<pre>";print_r($result);
               // die;
                //BankTemp::insert($result);
			}

		return view('admin.refund.refund-list',compact('breadcrumbs'));
	}

	
}
