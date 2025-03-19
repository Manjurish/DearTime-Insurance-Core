@php
$hashed_string = hash_hmac('SHA256', config('payment.user_name') . urldecode($user->uuid), config('payment.secret'));
@endphp

<html>
<body onload="document.order.submit()">
<h1>Please Wait ...</h1>
<form name="order" method="post" action="{{config('payment.base_url')}}/tokenization/{{config('payment.user_name')}}">
    <input type="hidden" name="order_id" value="{{$user->uuid}}">
    <input type="hidden" name="name" value="{{$user->profile->name}}">
    <input type="hidden" name="email" value="{{$user->email}}">
    <input type="hidden" name="phone" value="{{$user->phone}}">
    <input type="hidden" name="hash" value="{{$hashed_string}}">
</form>
</body>
</html>



@php
//# this part is to process the response received from senangPay, make sure we receive all required info
//else if(isset($_GET['order_id'])
//&& isset($_GET['status_id'])
//&& isset($_GET['token'])
//&& isset($_GET['cc_num'])
//&& isset($_GET['cc_type'])
//&& isset($_GET['msg'])
//&& isset($_GET['hash'])
//) {
//    # verify that the data was not tempered, verify the hash
//    $string = sprintf(
//        '%s%s%s%s%s%s%s',
//        $merchant_id,
//        urldecode($_GET['order_id']),
//        urldecode($_GET['status_id']),
//        urldecode($_GET['token']),
//        urldecode($_GET['cc_num']),
//        urldecode($_GET['cc_type']),
//        urldecode($_GET['msg'])
//    );
//    $hashed_string = hash_hmac('SHA256', $string, $secretkey);
//
//    # if hash is the same then we know the data is valid
//    if($hashed_string == urldecode($_GET['hash']))
//    {
//        # this is a simple result page showing either the card was successfully validated or failed. In real life you will need
//        # to save the token based on your order_id
//        if(urldecode($_GET['status_id']) == '1')
//            echo 'Card successfully validated with message: '.urldecode($_GET['msg']);
//        else
//            echo 'Failed to validate card with message: '.urldecode($_GET['msg']);
//    }
//    else
//        echo 'Hashed value is not correct';
//
//}

@endphp


