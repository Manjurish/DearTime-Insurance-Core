<?php     

namespace App\Http\Controllers\Admin;

use App\Claim;
use App\Coverage;
use App\Document;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use App\Order;
use App\Transaction;
use App\SpoCharityFunds;
use App\SpoCharityFundApplication;
use App\User;
use Carbon\Carbon;
use File;
use Image;
use Response;

class DashboardController extends Controller
{
	public function main()
	{
		$labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

		// monthly user joined
		$title  = 'Monthly User Joined ' . Carbon::now()->year;
		$users   = [];

		for ($i = 1; $i <= 31; $i++) {
			$users[] = User::where(\DB::raw("DATE_FORMAT(created_at, '%m')"),$i)->count();
		}

		// monthly order
		$title  = 'Monthly Order ' . Carbon::now()->year;
		$orders   = [];

		for ($i = 1; $i <= 31; $i++) {
			$orders[] = Order::where('status',Enum::ORDER_SUCCESSFUL)->where(\DB::raw("DATE_FORMAT(created_at, '%m')"),$i)->count();
		}

		// total payed
		$totalPremiumReceived = Transaction::where('success',1)->sum('amount');

		// user
		$individualCount = User::where('type',Enum::USER_TYPE_INDIVIDUAL)->count();
		$corporateCount  = User::where('type',Enum::USER_TYPE_CORPORATE)->count();
		$claimsCount     = Claim::count();
		$charityfundsum =0;
		//$sop=SpoCharityFunds::all();
		$sop=SpoCharityFunds::where('status','ADDED')->get();
		$amounts=$sop->pluck('charity_fund');

		
		foreach($amounts as $key=>$value){
			if(isset($value))
            $charityfundsum += $value;
		  }

		  $approvedfund =Transaction::where('gateway','sponsored_insurance')->sum('amount')?? 0;
		  $charityfundsum =$charityfundsum - $approvedfund;
		  if($charityfundsum < 0){
            $charityfundsum =0;
          }

			//dd($approvedfund);

		  $soponhold_fund =SpoCharityFunds::where('status','ON HOLD')->sum('charity_fund');
		  
		  $sopcovered=SpoCharityFundApplication::where('status','ACTIVE')->count();
		  $sopinline =SpoCharityFundApplication::where('status','QUEUE')->count();

		  return view('pages.dashboard-internal',compact('totalPremiumReceived','individualCount','corporateCount','claimsCount','charityfundsum','sopcovered','sopinline','soponhold_fund'))
		  ->with('labels',json_encode($labels))
		  ->with('users',json_encode($users,JSON_NUMERIC_CHECK))
		  ->with('orders',json_encode($orders,JSON_NUMERIC_CHECK));
	}

    public function showDocument($path,$ext)
    {

        $document = Document::where("url",$path);
        $document = $document->get()->first();

        empty(($document));
        $path = $document->S3Url;
        empty(($path));

        if(!auth('internal_users')->check()){
            //accessDenied($document->created_by != auth()->id());
        }

        $type = File::mimeType($path);

        $response = Response::make($path, 200);
//        $response->header("Content-Type", $type);

        return $response;
    }

	
	public function empty($resource) {
        return false;
    }

	

	public function showDocumentResize($type,$path,$ext)
	{
		//var_dump($path);
		$document = Document::where("url",$path);
		$document = $document->get()->first();
		//dd($document);

		empty(($document));

		$path = $document->S3Url;
		//empty(($path));
		//dd($path);

		$img = ['png' => 'image/png','jpg' => 'image/jpeg','gif' => 'image/gif','webp' => 'image/webp'];
		if(!in_array($document->ext,array_keys($img))){
			//response as file
			$content_type = [
				'pdf'  => 'application/pdf',
				'docx' => 'application/vnd.ms-word',
				'doc'  => 'application/vnd.ms-word',
				'xls'  => 'application/vnd.ms-excel',
				'xlsx' => 'application/vnd.ms-excel',
			];

			$headers = [
				'X-Vapor-Base64-Encode' => TRUE,
				'Content-type'          => $content_type[$document->ext] ?? '',
				'Content-Disposition'   => 'attachment; filename="' . $document->name . '"',
			];
			return response()->make($path,200,$headers);
		}

		$mime	=	$img[$document->ext];

		//        if(!auth('internal_users')->check()){
		//            if($document->created_by != auth()->id() && $document->type != 'selfie')
		//                abort(403);
		//        }

		switch ($type) {
			case 'tiny':
				$width = '100';
				break;
			case 'small':
				$width = '200';
				break;
			case 'medium':
				$width = '300';
				break;
			default :
				$width = '500';
		}

		if($type == 'thumb'){

			$response = Response::make($document->ThumbS3Url,200);
			$response->header("Content-Type",$mime);

			return $response;
		}

//		$img = Image::cache(function ($image) use ($type,$width,$path) {
//			$image->make($path);
//
//			if($type == 'square')
//				$image->resize($width,$width);
//
//			elseif($type != 'actual')
//				$image->resize($width,NULL,function ($constraint) {
//					$constraint->aspectRatio();
//				});
//
//		},100,TRUE);

        $response = Response::make($document->S3Url,200);
        $response->header("Content-Type",$mime);

        return $response;
//		return $img->response();
	}
}
