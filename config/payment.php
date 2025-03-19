<?php     

return [
    'base_url' => env('SENANG_BASE_URL'),
    'user_name' => env('SENANG_USER_NAME'),
    'password' => env('SENANG_PASSWORD'),
    'secret' => env('SENANG_SECRET'),
    'kipple' => [
        'appId' => '80000001',
        'appKey' =>  '27e713e301e83a8f',
        'base_url' => 'https://uat.kiplepay.com/api/v3.0/'
    ],
    'senangpay' => [
        'merchant_id' => '734165884262855',
        'secret_key' =>  '4850-836',
        'base_url' => 'https://sandbox.senangpay.my/apiv1/',
        'tokenization_url' => 'https://sandbox.senangpay.my/tokenization/',
        'checkout_url' => 'https://sandbox.senangpay.my/payment/'
    ]
];
