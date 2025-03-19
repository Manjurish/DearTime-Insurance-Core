<?php     

namespace App\Http\Controllers\Admin;

use App\Coverage;
use App\CoverageOrder;
use App\Helpers;
use App\Http\Controllers\Controller;
use App\Transaction;
use Carbon\Carbon;
use Mmeshkatian\Ariel\BaseController;

class PaymentController extends Controller
{
	public function index()
	{
		return view('admin.transactions.index');
	}
    public function configure()
    {
        $this->model = Transaction::class;
        $this->setTitle("Transactions");

        $this->addColumn("Transaction Reference",'transaction_ref');
        $this->addColumn("Transaction Id",'transaction_id');
        $this->addColumn("Amount",function ($q){
            return 'RM'.$q->amount;
        });
        $this->addColumn("Card Type",'card_type');
        $this->addColumn("Card No.",'card_no');
        $this->addColumn("Order Id",'order.ref_no');
        $this->addColumn("Date",function ($q){
            return Carbon::parse($q->date)->format('d/m/Y H:i A');
        });
        $this->addColumn("Status",function ($q){
            return $q->success?'Successful':'Unsuccessful';
        });
        $this->addColumn("Created",function ($query){
            return Carbon::parse($query->created_at)->format('d/m/Y H:i A');
        });

        $this->addBladeSetting('hideCreate',true);
        $this->addAction('admin.CoverageOrder.index','<i class="feather icon-clipboard"></i>','Order Detail',['order'=>'$id'],Helpers::getAccessControlMethod());

        return $this;

    }

}
