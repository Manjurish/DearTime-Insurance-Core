<?php     

namespace App\Http\Controllers\Admin;

use App\CoverageOrder;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class CoverageOrderController extends Controller
{
    public function index()
    {
        $orderId 	= 	(int)\request()->input('order_id');
        return view('admin.coverageorders.index')->with('orderId', $orderId);
    }

    public function configure()
    {
        $this->model = CoverageOrder::class;
        $this->setTitle("Coverage Order");
        $orderId = (int)\request()->input('order');
        $this->addQueryCondition('where',['order_id',$orderId]);
        $this->addColumn("Coverage Ref No",function ($query){
            return $query->coverage->ref_no;
        });

        $this->addColumn("Owner",function ($query){
            return $query->coverage->owner->name;
        });

        $this->addColumn("Payer",function ($query){
            return $query->coverage->payer->profile->name;
        });

        $this->addColumn("Covered",function ($query){
            return $query->coverage->covered->name;
        });

        $this->addColumn("Product",function ($query){
            return $query->coverage->product_name;
        });

        $this->addColumn("Status",function ($query){
            return $query->coverage->status;
        });

        $this->addColumn("Payment Term",function ($query){
            return $query->coverage->payment_term;
        });

        $this->addColumn("Created",function ($query){
            return Carbon::parse($query->created_at)->format('d/m/Y H:i A');
        });

        $this->addBladeSetting('hideCreate',true);

        return $this;
    }
}
