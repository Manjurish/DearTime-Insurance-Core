<?php     

namespace App\Http\Controllers\Api;

use App\Address;
use App\Country;
use App\Coverage;
use App\Helpers;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use App\Jobs\CancelPendingCoverage;
use App\Jobs\PromoterRefund;
use App\Notifications\Email;
use App\Notifications\EmailPromoter;
use App\Notifications\EmailVerification;
use App\Order;
use App\User;
use App\UserNotificationToken;
use App\SpoCharityFundApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;


class ToolsController extends Controller
{
    public function initPostRegisterIndividual(Request $request)
    {

        $request->initial = 'true';
        $user = auth('internal_users')->user() ?? auth('api')->user() ?? auth()->user();
        $charity =false;
        ////unAuthorized(empty($user)); 
        if($user->profile->is_charity()){
            $application = SpoCharityFundApplication::where('user_id',$user->profile->user_id)->whereIn('status',['ACTIVE','PENDING','SUBMITTED','QUEUE'])->first();
            if($application){
            if($application->status !='ACTIVE'){
                $charity =true;
            }else{
                $charity =false;
            }
        }
            
        }else{
            $charity =false;
        }

        return ['status' => 'success',
            'data' => [
                'profile' => $user->profile,
                'charity' => $charity,
                'states' => (new AddressController())->stateList($request),
                'industries' => (new IndustryJobsController())->getList($request),
                'nationalities' => Country::select('uuid', 'nationality', 'is_allowed')->get(),
            ]
        ];
    }

    public function sendTestNotification(Request $request){
        $input = $request->all();
        $userId = $input['user_id'] ?? dd('You need user_id parameters');
        $title = $input['title'] ?? 'default title';
        $body = $input['body'] ?? 'default body';

        $token = UserNotificationToken::where('user_id',$userId)->latest()->first();
        $tokenId = $token->token ?? dd('user not found');

        $modal=[
            "buttons" => [
                [
                    "title" => __('mobile.buy_for_others'),
                    "action" => NextPage::ADD_NOMINEE,
                    "type" => "page",
                ],
                [
                    "title" => __('mobile.invite'),
                    "action" => "",
                    "type" => "",
                ]
            ]
        ];

        $data = $input['data'] ?? Helpers::response('success',Enum::PAGE_ACTION_TYPE_MODAL,$modal);


        if($token->os == 'ios'){
            Helpers::sendNotificationToIOS($title,$body,$data,$tokenId);
        }
        else{
            Helpers::sendNotificationToAndroid($title,$body,$data,$tokenId);
        }

        dd('Success Send Notification');
    }

    public function test(){
        $a = route('doc.view',['app_view' => Helpers::isFromApp() ? '1' : '2','type' => 'consent','claimUuid'=>'694368c0-5d27-4b1b-80ea-017b810a5909','uuid'=>encrypt('d5af52ac-2967-402c-a1d2-00e14440fed4'),'need_save'=>true]);
        dd($a);
    }
}
