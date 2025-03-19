<?php     

namespace App\Http\Controllers\User;

use App\BankAccount;
use App\BankCard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts   = auth()->user()->profile->bankAccounts;
        $cards      = auth()->user()->profile->bankCards;
        $hasData    = $accounts->count() > 0 || $cards->count() > 0;
        $auth_url   =  url('/payment/authenticate/'.Auth::user()->uuid.'/web');
        $show_card  = true;
        $skipBankDetails = !auth()->user()->profile->needBankAccount();
        $isFlowDone = auth()->user()->isFlowDone();
        $isFlowDone = false;

        return view('user.bankaccount_list',compact('accounts','cards','hasData','auth_url','show_card','skipBankDetails','isFlowDone'));
    }

    public function setFundSource(Request $request)
    {
        $this->validate($request,[
           'fund_source' => 'required|in:self_employed_income,income_from_employment,investment,savings,inheritance'
        ]);
        $fund_source    = $request->input('fund_source');
        $profile        = auth()->user()->profile;
        if(!empty($profile)){
            $profile->fund_source = $fund_source;
            $profile->save();
        }
        return "1";
    }

    public function indexAccount()
    {
        $show_bank = true;
        $accounts = auth()->user()->profile->bankAccounts;
        $cards = auth()->user()->profile->bankCards;
        $hasData = $accounts->count() > 0 || $cards->count() > 0;
        $auth_url =  url('/payment/authenticate/'.Auth::user()->uuid.'/web');
        return view('user.bankaccount_list',compact('accounts','cards','hasData','auth_url','show_bank'));
    }


    public function store(Request $request)
    {
        $this->validate($request,[
            'account_no'    => ['required','max:20','min:8',(new Unique('bank_accounts','account_no'))->ignore(auth()->user()->profile->bankAccounts->first()->account_no ?? null,'account_no')],
            'bank_name'     => 'required|string|max:100',
        ]);

        $bankAccount = auth()->user()->profile->bankAccounts()->get()->first() ?? (new BankAccount());
        $bankAccount->account_no = $request->input('account_no');
        $bankAccount->bank_name = $request->input('bank_name');

        auth()->user()->profile->bankAccounts()->save($bankAccount);

        if($request->has('mn')) {
            return redirect()->back()->with("success_alert", __('mobile.bank_account_saved'));
        }

        return redirect()->route('userpanel.Verification.index')->with("success_alert",__('web/bank.add_success'));

    }
    public function storeCard(Request $request)
    {
        $request->validate([
            'cc'            => 'required|max:16|min:16', //|unique:bank_cards,cc
            'cvv'           => 'required|string|max:100',
            'expiry_date'   => 'required|string|max:100',
        ]);

        $bankCard = new BankCard();
        $bankCard->cc       = $request->cc;
        $bankCard->cvv      = $request->cvv;
        $bankCard->expiry   = $request->expiry_date;

        auth()->user()->profile->bankCards()->save($bankCard);

        return redirect()->back()->with("success_alert",__('web/bank.add_card_success'));
    }

    public function destroy($id)
    {
        auth()->user()->profile->bankAccounts()->where("uuid",$id)->delete();
        auth()->user()->profile->bankCards()->where("uuid",$id)->delete();
        return redirect()->back()->with("success_alert",__('web/bank.remove_success'));
    }


}
