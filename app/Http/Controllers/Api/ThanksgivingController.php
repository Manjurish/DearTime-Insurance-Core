<?php     

namespace App\Http\Controllers\Api;


use App\Helpers\NextPage;
use App\Thanksgiving;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;



class ThanksgivingController extends Controller
{

    /**
     * @api {get} api/thanksgiving get thanksgiving
     * @apiVersion 1.0.0
     * @apiName GetThanksgiving
     * @apiGroup Thanksgiving
     *
     * @apiUse AuthHeaderToken
     *
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {Object[]} data[thanksgiving]
     * @apiSuccess (Response (200) ) {String} thanksgiving[uuid]
     * @apiSuccess (Response (200) ) {String} thanksgiving[type]
     * @apiSuccess (Response (200) ) {Number} thanksgiving[percentage]
     * @apiSuccess (Response (200) ) {String} data[promoter_allowed]
     * @apiSuccess (Response (200) ) {String} data[promoter_name]
     * @apiSuccess (Response (200) ) {String} data[myself_allowed]
     *
     * @apiSuccessExample {json} Success Response:
     *{
     *   "status": "success",
     *   "data": {
     *       "thanksgiving": [
     *           {
     *               "uuid": "fbc20b9b-20cd-40b2-ad0c-263bce826500",
     *               "type": "Charity Insurance",
     *               "percentage": 100
     *           }
     *       ],
     *       "promoter_allowed": false,
     *       "promoter_name": "",
     *       "myself_allowed": false
     *   }
     *}
     *
     * @apiError {String} status error
     * @apiError {String} message
     */

    public function get(Request $request)
    {
        $user = $request->user();
        if(empty($user))
            $user = auth()->user();
        $myself =Thanksgiving::where('individual_id',$user->profile->id)->whereType('self')->first();
        if( $user->profile->household_income > 5300){
           $user->profile->thanksgiving()->whereType('charity')->update(['percentage' => 100]);
            if($myself){
                if($myself->percentage == 100){
                     $charityper =Thanksgiving::where('individual_id',$user->profile->id)->whereType('charity')->first();
                     $charityper->percentage = 100;
                     $charityper->save();
                    }
                }
        }
       if($user->profile->household_income <= 5300){
            if($myself){
            $user->profile->thanksgiving()->whereType('charity')->update(['percentage' => (100-($myself->percentage))]);
            }
        }
    
        $promoter =Thanksgiving::where('individual_id',$user->profile->id)->whereType('promoter')->first();

        if($myself && $promoter){
            $user->profile->thanksgiving()->whereType('charity')->update(['percentage' => (100-($myself->percentage + $promoter->percentage))]);
           }

       elseif($promoter){
            $user->profile->thanksgiving()->whereType('charity')->update(['percentage' => (100-($promoter->percentage))]);
            }

         return ['status' => 'success', 'data' => ['thanksgiving' => $user->profile->thanksgiving()->where('percentage','>',0)->get(), 'promoter_allowed' => (($user->from_referrer ?? null) != null &&  !$user->profile->is_charity()),'promoter_name'=>User::find($user->from_referrer)->name ?? '', 'myself_allowed' => ($user->profile->household_income <= 5300 && !$user->profile->is_charity())]];
    }



    /**
     * @api {post} api/thanksgiving set thanksgiving
     * @apiVersion 1.0.0
     * @apiName SetThanksgiving
     * @apiGroup Thanksgiving
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {Number} self
     * @apiParam (Request) {Number} [charity]
     * @apiParam (Request) {Number} [promoter]
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {Array} data
     * @apiSuccess (Response (200) ) {String} data[next_page]
     * @apiSuccess (Response (200) ) {Object} data[thanksgiving]
     *
     * @apiError {String} status error
     * @apiError {String} message
     */
    public function set(Request $request)
    {
        $user = $request->user();
        if(empty($user))
            $user = auth()->user();

        $user = $user->profile;


        $request->validate([
            'self' => 'required|numeric',
            'charity' => 'numeric',
            'promoter' => 'numeric',
        ]);

    //added for remove card in corporate flow
    $check_coverage_offered = $user->coverages_owner()->whereIn('status',['unpaid','increase-unpaid','decrease-unpaid'])->where('state','inactive')->latest()->first()->payer_id ?? null;
    $check_coverage_by_corp = User::where('id',$check_coverage_offered)->first()->corporate_type ?? null;
    $check_user_id = $user->id;
    if($check_coverage_offered != null){
        $corp_individual_check = ($check_coverage_offered != $user->id) && $check_coverage_by_corp=='payorcorporate';
    }else { $corp_individual_check = false; }


        $user->thanksgiving()->delete();

        if($request->self > 0) {
            $tg = new Thanksgiving();
            $tg->type = 'self';
            $tg->percentage = $request->self;
            $user->thanksgiving()->save($tg);
        }

        $tg = new Thanksgiving();
        $tg->type = 'charity';
        $tg->percentage = $request->charity;
        $user->thanksgiving()->save($tg);

        if ($request->user()->from_referrer != null) {
            $tg = new Thanksgiving();
            $tg->type = 'promoter';
            $tg->percentage = $request->promoter;
            $user->thanksgiving()->save($tg);
        } else {
            $user->thanksgiving()->whereType('charity')->update(['percentage' => 100 - $request->self]);
        }

        $charity = $user->nominees()->whereEmail('Charity@Deartime.com')->first()->percentage ?? 0;
        if($user->is_charity() || $corp_individual_check){
            $next_page = NextPage::PAYMENT_DETAILS_ACCOUNT;
        }else{
                $next_page = NextPage::PAYMENT_DETAIL;     
        }
        
        if($charity == '100'){
            if($user->is_charity() || $corp_individual_check){
                $next_page = NextPage::PAYMENT_DETAILS_ACCOUNT;
            }else{
                
                    $next_page = $user->isVerified() ? NextPage::ORDER_REVIEW : NextPage::VERIFICATION;


            }

        }

        return ['status' => 'success', 'data' => ['next_page' => $next_page,'thanksgiving' => $user->thanksgiving]];
//        ($user->bankAccounts || $user->bankCards) ? 'account_page' :
    }
    
      public function update(Request $request){

        $user = $request->user();
        if(empty($user))
            $user = auth()->user();

            $this->get($request);


            $thanks =Thanksgiving::where('individual_id',$user->profile->id)->get();


            $user->profile->thanksgiving()->whereType('self')->update(['deleted_at' => Carbon::now()]);

            return ['status' => 'success'];

    }
}
