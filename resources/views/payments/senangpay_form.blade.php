<html>

<head>
    <title>senangPay 3D Tokenization Add Card</title>
</head>

<body onload="document.order.submit()">
    <form name="order" method="post" action="{{ config('payment.senangpay.tokenization_url') . config('payment.senangpay.merchant_id'); }}">
        <input type="hidden" name="order_id" value="{{ $order_id }}">
        <input type="hidden" name="name" value="{{ $username }}">
        <input type="hidden" name="email" value="{{ $useremail }}">
        <input type="hidden" name="phone" value="{{ $userphone }}">
        <input type="hidden" name="hash" value="{{ $hashstring }}">
    </form>
</body>

</html>