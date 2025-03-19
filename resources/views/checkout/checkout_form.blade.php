<html>

<head>
    <title>senangPay Open API Checkout</title>
</head>

<body onload="document.order.submit()">
    <form name="order" method="post" action="<?php echo $checkout_url; ?>">
        <input type="hidden" name="detail" value="<?php echo $form_detail; ?>">
        <input type="hidden" name="amount" value="<?php echo $form_amount; ?>">
        <input type="hidden" name="order_id" value="<?php echo $form_order_id; ?>">
        <input type="hidden" name="name" value="<?php echo $form_name; ?>">
        <input type="hidden" name="email" value="<?php echo $form_email; ?>">
        <input type="hidden" name="phone" value="<?php echo $form_phone; ?>">
        <input type="hidden" name="hash" value="<?php echo $hashed_string; ?>">
    </form>
</body>

</html>