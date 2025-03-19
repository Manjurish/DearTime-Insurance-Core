<?php     

namespace App\Http\Controllers\Api;


use App\BankAccount;
use App\BankCard;
use App\Http\Controllers\User\PaymentGatewayController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class BankCardController extends Controller
{
    public function add(Request $request)
    {
        $user = Auth::user()->profile;

        $request->validate([
            'cc' => 'required|max:16|min:16', //|unique:bank_cards,cc
            'cvv' => 'required|string|max:4',
            'expiry_date' => 'required|string|max:5',
        ]);


        $bankCard = new BankCard();
        $bankCard->cc = $request->cc;
        $bankCard->cvv = $request->cvv;
        $bankCard->expiry = $request->expiry_date;

        $user->bankCards()->save($bankCard);

        return ['status' => 'success', 'message' => __('web/messages.bank_added'), 'data' => $bankCard];

    }

    public function delete(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string|exists:bank_cards,uuid',
        ]);
        $user = Auth::user()->profile;
        $gateway = new PaymentGatewayController();
        $gateway->deleteCard(Auth::user());
//        $user->bankCards()->whereUuid($request->uuid)->delete();

        return ['status' => 'success', 'message' => __('web/messages.bank_removed'), 'data' => $user->bankCards];

    }
}
