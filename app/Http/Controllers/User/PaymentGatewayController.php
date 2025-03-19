<?php

namespace App\Http\Controllers\User;


use App\BankCard;
use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use App\Individual;
use App\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\PaymentResponseLogs;

class PaymentGatewayController extends Controller
{
    public function getToken($id, $platform)
    {
        $user = User::whereUuid($id)->firstOrFail();
        if ($platform == 'web')
            session()->put('add_credit_card_platform', 'web');

        $merchant_id    =   config('payment.senangpay.merchant_id');
        $order_id       =   $user->uuid . '_' . time();
        $hashed_string  =   hash_hmac('SHA256', $merchant_id . $order_id, config('payment.senangpay.secret_key'));

        $username       =   $user->profile->name;
        $useremail      =   $user->email;
        $userphone      =   $user->profile->mobile;
        $hashstring     =   $hashed_string;

        $payload = [
            'order_id',
            'username',
            'useremail',
            'userphone',
            'hashstring'
        ];

        return view('payments.senangpay_form')->with(compact($payload));
    }


    public function return_post(Request $request)
    {
        $payment_status     =   false;
        $merchant_id        =   config('payment.senangpay.merchant_id');
        $payment_status_id  =   urldecode($request->status_id);

        $user_id = explode('_', urldecode($request->order_id));
        array_pop($user_id);
        $user = User::whereUuid($user_id)->firstOrFail();

        # verify that the data was not tempered, verify the hash
        $string = sprintf(
            '%s%s%s%s%s%s%s',
            $merchant_id,
            urldecode($request->order_id),
            urldecode($payment_status_id),
            urldecode($request->token),
            urldecode($request->cc_num),
            urldecode($request->cc_type),
            urldecode($request->msg)
        );
        $hashed_string = hash_hmac('SHA256', $string, config('payment.senangpay.secret_key'));

        # if hash is the same then we know the data is valid
        if ($hashed_string == urldecode($request->hash)) {
            if ($payment_status_id == '1') {    //SUCCESS
                //VALIDATE TOKEN
                $validate_response = Http::withBasicAuth($merchant_id, '')
                                     ->post(config('payment.senangpay.base_url') . 'validate_token', [
                                        'token' => urldecode($request->token)
                                    ]);
                if ($validate_response->successful()) {
                    $validate_json          =   json_decode($validate_response->body(), true);
                    if ($validate_json['status'] == '1') {
                        $payment_status         =   true;
                        $bankCard               =   new BankCard();
                        $bankCard->token        =   urldecode($request->token);
                        $user->profile->bankCards()->save($bankCard);

                        $bankCard->saved_date   =   Carbon::now();
                        $bankCard->scheme       =   urldecode($request->cc_type);
                        $bankCard->masked_pan   =   str_pad(urldecode($request->cc_num), 16, 'X', STR_PAD_LEFT);
                        $bankCard->holder_name  =   'XXXXXXXXXX';
                        $bankCard->expiry_month =   '';
                        $bankCard->expiry_year  =   '';
                        $bankCard->code         =   '';
                        $bankCard->message      =   urldecode($request->msg);
                        $bankCard->save();
                    }
                }             
            }
        }

        // add action
		$actions = [
			'methods'  => '',
			'added_at' => Carbon::now()->format(config('static.datetime_format')),
		];

		$user->actions()->create([
			'user_id' => $user->id,
			'type'    => Enum::ACTION_TYPE_AMENDMENT,
			'event'   => Enum::ACTION_EVENT_ADD_BANK_CARD,
			'actions' => $actions,
			'execute_on' => Carbon::now(),
			'status'  => Enum::ACTION_STATUS_EXECUTED
		]);

        return view('payments.senang_authenticate_callback')->with(['status' => $payment_status ? 'success' : 'cancel']);
    }

    public function callback(Request $request)
    {
        $payment_status     =   false;
        $merchant_id        =   config('payment.senangpay.merchant_id');
        $payment_status_id  =   urldecode($request->status_id);

        $user_id = explode('_', urldecode($request->order_id));
        array_pop($user_id);
        $user = User::whereUuid($user_id)->firstOrFail();

        # verify that the data was not tempered, verify the hash
        $string = sprintf(
            '%s%s%s%s%s%s%s',
            $merchant_id,
            urldecode($request->order_id),
            urldecode($payment_status_id),
            urldecode($request->token),
            urldecode($request->cc_num),
            urldecode($request->cc_type),
            urldecode($request->msg)
        );
        $hashed_string = hash_hmac('SHA256', $string, config('payment.senangpay.secret_key'));

        # if hash is the same then we know the data is valid
        if ($hashed_string == urldecode($request->hash)) {
            if ($payment_status_id == '1') {    //SUCCESS
                //VALIDATE TOKEN
                $validate_response = Http::withBasicAuth($merchant_id, '')
                                     ->post(config('payment.senangpay.base_url') . 'validate_token', [
                                        'token' => urldecode($request->token)
                                    ]);
                if ($validate_response->successful()) {
                    $validate_json          =   json_decode($validate_response->body(), true);
                    if ($validate_json['status'] == '1') {
                        $payment_status         =   true;
                        $bankCard               =   new BankCard();
                        $bankCard->token        =   urldecode($request->token);
                        $user->profile->bankCards()->save($bankCard);

                        $bankCard->saved_date   =   Carbon::now();
                        $bankCard->scheme       =   urldecode($request->cc_type);
                        $bankCard->masked_pan   =   str_pad(urldecode($request->cc_num), 16, 'X', STR_PAD_LEFT);
                        $bankCard->holder_name  =   'XXXXXXXXXX';
                        $bankCard->expiry_month =   '';
                        $bankCard->expiry_year  =   '';
                        $bankCard->code         =   '';
                        $bankCard->message      =   urldecode($request->msg);
                        $bankCard->save();
                    }
                }             
            }
        }

        // add action
		$actions = [
			'methods'  => '',
			'added_at' => Carbon::now()->format(config('static.datetime_format')),
		];

		$user->actions()->create([
			'user_id' => $user->id,
			'type'    => Enum::ACTION_TYPE_AMENDMENT,
			'event'   => Enum::ACTION_EVENT_ADD_BANK_CARD,
			'actions' => $actions,
			'execute_on' => Carbon::now(),
			'status'  => Enum::ACTION_STATUS_EXECUTED
		]);

        exit('OK');
    }

    public function deleteCard($user)
    {
        $card = $user->profile->bankCards()->latest()->first();
        if ($card) {
            $merchant_id        =   config('payment.senangpay.merchant_id');
            Http::withBasicAuth($merchant_id, '')
                ->post(config('payment.senangpay.base_url') . 'update_token_status', [
                'token' => $card->token
            ]);
        }
        $user->profile->bankCards()->latest()->delete();

        // add action
        $actions = [
            'methods'  => '',
            'deleted_at' => Carbon::now()->format(config('static.datetime_format')),
        ];

        $user->actions()->create([
            'user_id' => $user->id,
            'type'       => Enum::ACTION_TYPE_AMENDMENT,
            'event'   => Enum::ACTION_EVENT_DELETE_BANK_CARD,
            'actions' => $actions,
            'execute_on' => Carbon::now(),
            'status'  => Enum::ACTION_STATUS_EXECUTED
        ]);

        //        if($json->success == 1){
        //            $user->profile->bankCards()->delete();
        //        }
    }

    public function pay($order_id)
    {
        $order = Order::whereUuid($order_id)->firstOrFail();
        $payer = $order->payer;

        $url = config('payment.kipple.base_url') . 'creditCard/card-payment';
        $card = $payer->profile->bankCards()->latest()->first();

        $payload = [
            'response_url' => url('/payment/webhook'),
            'amount' => "10.59",
            'reference' => 'TRX' . random_int(10000, 99999),
            'third_party_user_id' => $payer->uuid,
            'card_unique_token' => $card->token
        ];

        $params = $this->prepReq($payload);

        $response = Http::asForm()->post($url, $params);
        //  return $card->token;
        $json = json_decode($response->body());
    }
}