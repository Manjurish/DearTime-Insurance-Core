<?php     

namespace App\Http\Controllers\Api;

use App\Action;
use App\Company;
use App\Helpers\Enum;
use App\Helpers\NextPage;
use App\Http\Controllers\User\MedicalSurveyController;
use App\Individual;
use App\IndustryJob;
use App\Notifications\EmailPromoter;
use App\Refund;
use App\Rules\UniqueInModel;
use App\Thanksgiving;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;


class PromoterController extends Controller
{
    public function add(Request $request)
    {
        $user = $request->user();
        if(empty($user))
            $user = auth()->user();

        $validator = [
            'name' => 'required|string',
            'passport' => 'required',
            'dob' => 'required|date_format:d/m/Y',
            'nationality' => 'required|string',
            'mobile' => 'required'.(empty($request->input('userId')) ? '|unique:individuals,mobile' : ''),
            'gender' => 'required|in:male,female',
            'type' => 'required|in:individual,corporate',
            'personal_income' => 'required|numeric|min:0|max:10001',
            'household_income' => 'required|numeric|min:0|max:10001',
            'occ' => 'required|exists:industry_jobs,uuid',
            'passport_expiry_date' => 'required_unless:nationality,Malaysian|nullable|date_format:d/m/Y',
        ];
        if(empty($request->input('userId'))){
            $validator['email'] = ['required','email','unique:users,email'];
        }else{
            $validator['email'] = ['required','email'];

        }

        $request->validate($validator);


        $new_user = User::OnlyPendingPromoted()->where("uuid",$request->input('userId'));
        if($new_user->count() == 0 ) {
            $new_user = new User();
            $newUserReg = true;
        }else
            $new_user = $new_user->first();

        $new_user->email = $request->input('email');
        $new_user->password = null;
        $new_user->type = $request->input('type');
        $new_user->activation_token = Str::uuid()->toString();
        $new_user->promoter_id = $user->id;
        $new_user->save();
        if(!empty($newUserReg)) {
            try {
                $new_user->notify(new EmailPromoter($user->profile->name ?? 'a Promoter User', $new_user->uuid));
            } catch (\Exception $e) {

            }
        }

        $type = $request->input('type');
        if($type == 'individual'){
            $individual = Individual::where("user_id",$new_user->id)->count();
            if($individual == 0) {
                $dob = Carbon::createFromFormat("d/m/Y",$request->input('dob'));
                $passport_expiry_date = null;
                if(!empty($request->input('passport_expiry_date')))
                    $passport_expiry_date = Carbon::createFromFormat("d/m/Y",$request->input('passport_expiry_date'));
                $individual = new Individual();
                $individual->user_id = $new_user->id;
                $individual->name = $request->input('name');
                $individual->gender = ucfirst($request->input('gender'));
                $individual->dob = $dob;
                $individual->nationality = $request->input('nationality');
                $individual->household_income = $request->input('household_income');
                $individual->personal_income = $request->input('personal_income');
                $occ = IndustryJob::where("id",$request->input('occ'));
                if($occ->count() > 0){
                    $occ = $occ->first()->id ?? 0;
                }else{
                    $occ = IndustryJob::where("uuid",$request->input('occ'));
                    $occ = $occ->first()->id ?? 0;
                }
                $individual->occ = $occ ?? 0;
                $individual->passport_expiry_date = $passport_expiry_date;
                $individual->nric = $request->input('passport');
                $individual->mobile = str_replace("+60", "", $request->input('mobile'));
                $individual->save();
            }
        }else{
            $corporate = Company::where("user_id",$new_user->id)->count();
            if($corporate == 0) {
                $corporate = new Company();
                $corporate->user_id = $new_user->id;
                $corporate->name = $request->input('name');
                $corporate->reg_no = str_replace("+60", "", $request->input('mobile'));
                $corporate->corporate_verified = 0;
                $corporate->save();
            }
        }

        return ['status' => 'success', 'data' => ['next_page' => 'underwriting_page','next_page_params'=>['user_id'=>$new_user->uuid,'user_name'=>$new_user->profile->name ?? 'Promoted User'],'next_page_url'=>route('userpanel.promote.medicalSurvey',$new_user->uuid)]];
    }

    public function list(Request $request)
    {
        $user = $request->user();
        if(empty($user))
            $user = auth()->user();

        $promoteds = $user->referrer()->WithPendingPromoted()->get()->makeVisible('created_at');
        $promotedArray = [];
        foreach ($promoteds as $promoted){
            $thanksgiving=Thanksgiving::whereType('promoter')->where('individual_id',$promoted->profile->id)->latest()->first();
            array_push($promotedArray,[
                'uuid'=>$promoted->uuid,
                'register_on'=>Carbon::parse($promoted->created_at)->format(config('static.date_format')),
                'name'=>$promoted->profile->name,
                'thanksgiving'=>$thanksgiving,
            ]);
        }

        return ['status' => 'success', 'data' => [
            'promoted'=>$promotedArray,
            'share_text'=>__('web/messages.share_text',['link'=>'https://deartime/'.NextPage::REGISTER.'?referrer='.$user->uuid])
        ]];
    }

    public function paymentsReceived(Request $request){
        $actions = Action::where('user_id',$request->user()->id)
            ->whereBetween('created_at',[now()->addDays(-30),now()])
            ->where('type',Enum::ACTION_TYPE_PROMOTER_REFUND)
            ->get();

        $resultRefund = [];
        foreach ($actions as $action){
            $refund = Refund::where('id',$action->actions['refund_id'])
                ->where('status',Enum::REFUND_STATUS_COMPLETED)
                ->first();
            if(!empty($refund)){
                $froms = [];
                foreach ($action->actions['credits'] as $credit){
                    $user = User::whereId($credit['from_id'])->first();
                    array_push($froms,$user->profile->name);
                }
                array_push($resultRefund,[
                    'amount'=>$refund->amount,
                    'created_at'=>Carbon::parse($refund->created_at)->format('F Y'),
                    'ref_no'=>$refund->pay_ref_no,
                    'account_no'=>$refund->bankAccounts->account_no,
                    'transaction_date'=>Carbon::parse($refund->effective_date)->format(config('static.datetime_second_format')),
                    'froms'=>array_unique($froms)
                ]);
            }
        }

        return ['status' => 'success', 'data' => $resultRefund];
    }


}
