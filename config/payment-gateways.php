<?php
use Kmalarifi\PaymentGateways\Gateways\HyperpayGateway;
use Kmalarifi\PaymentGateways\Gateways\MyFatoorahGateway;
return [

    /*--------------------------------------------------------------
    | Hosts (can point to Apigee / Kong / direct vendor URL)
    |-------------------------------------------------------------*/
    'hosts' => [
        'hyperpay' => env('HYPERPAY_API_URL', 'https://api.hyperpay.com'),
        'myfatoorah' => env('FATOORAH_API_URL', 'https://api.myfatoorah.com'),
        // add more vendors here…
    ],

    /*--------------------------------------------------------------
    | Endpoint paths for each provider
    |-------------------------------------------------------------*/
    'paths' => [

        'hyperpay' => [
            'checkout' => env('HYPERPAY_PATH_CHECKOUT', '/payment-gateway/v1/checkout'),
            'status' => env('HYPERPAY_PATH_STATUS', '/payment-gateway/v1/checkout/status'),
            'query' => env('HYPERPAY_PATH_QUERY', '/payment-gateway/v1/report/query'),
            'query_range' => env('HYPERPAY_PATH_QUERY_RANGE', '/payment-gateway/v1/report/query-range'),
        ],

        'myfatoorah' => [
            'initiate' => env('FATOORAH_PATH_INITIATE', '/v2/InitiatePayment'),
            'execute' => env('FATOORAH_PATH_EXECUTE', '/v2/ExecutePayment'),
            'status' => env('FATOORAH_PATH_STATUS', '/v2/GetPaymentStatus'),
        ],
    ],

    /*--------------------------------------------------------------
    | Gateway bindings
    |-------------------------------------------------------------*/
    'gateways' => [

        'hyperpay' => [
            'class' => HyperpayGateway::class,
            'credentials' => [
                'accessToken' => env('HYPERPAY_TOKEN'),
            ],
        ],

        'myfatoorah' => [
            'class' => MyFatoorahGateway::class::class,
            'credentials' => [
                'token' => env('MY_FATOORAH_TOKEN'),
            ],
        ],
    ],

    /*--------------------------------------------------------------
    | Default detector & default gateway (unchanged from earlier)
    |-------------------------------------------------------------*/
    'detector' => \App\Payment\MyRequestDetector::class,
    'default' => env('PAYMENT_GATEWAY', 'hyperpay'),
];
