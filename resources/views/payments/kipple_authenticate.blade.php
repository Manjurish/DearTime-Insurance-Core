@php


@endphp

<html>
<body onload="document.order.submit()">
<h1>Please Wait ...</h1>

<form name="order" method="post" action="{{config('payment.kipple.base_url')}}creditCard/add-card">

    <input type="hidden" name="appId" value="{{$appId}}">
    <input type="hidden" name="nonceStr" value="{{$nonceStr}}">
    <input type="hidden" name="params" value="{!! http_build_query($payload) !!}">
    <input type="hidden" name="timestamp" value="{{$timestamp}}">
    <input type="hidden" name="signature" value="{{$signature}}">

</form>
</body>
</html>
