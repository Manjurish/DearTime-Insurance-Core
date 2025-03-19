<?php     

return [

    'default' => 'staging',

    'connections' => [
        'staging' => [
            'host'      => env('TPA_HOST'),
            'username'  => env('TPA_USER'),
            'password'  => env('TPA_PASSWORD'),
        ],
    ],



];
