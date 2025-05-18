<?php
namespace Kmalarifi\PaymentGateways;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Kmalarifi\PaymentGateways\Contracts\PaymentGateway;
use Kmalarifi\PaymentGateways\Contracts\PaymentGatewayDetector;
use Kmalarifi\PaymentGateways\Exceptions\InvalidGatewayException;
use Kmalarifi\PaymentGateways\Gateways\HyperpayGateway;
use Kmalarifi\PaymentGateways\Gateways\MyFatoorahGateway;

class PaymentGatewaysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /* 1. Config */
        $this->mergeConfigFrom(__DIR__.'/../config/payment-gateways.php', 'payment-gateways');

    }


    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/payment-gateways.php' => config_path('payment-gateways.php'),
        ], 'payment-gateways-config');
    }
}
