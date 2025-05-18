<?php
namespace Kmalarifi\PaymentGateways;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Kmalarifi\PaymentGateways\Contracts\ResolveRequestPaymentGateway;
use Kmalarifi\PaymentGateways\Contracts\PaymentGatewayDetector;
use Kmalarifi\PaymentGateways\Exceptions\InvalidGatewayException;
use Kmalarifi\PaymentGateways\Gateways\HyperpayGateway;
use Kmalarifi\PaymentGateways\Gateways\MyFatoorahGateway;

/**
 * Resolves gateways *per HTTP request* using the user-supplied detector.
 * Keeps the main provider facade-friendly and uncluttered.
 */
class RequestGatewayResolverServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /* 1️⃣  Import package config (only if not already merged elsewhere) */

        /* 2️⃣  Bind the detector (singleton) */
        $this->app->singleton(PaymentGatewayDetector::class, fn ($app) =>
        $app->make(config('payment-gateways.detector'))
        );

        /* 3-A️⃣  Primary gateway (detect) */
        $this->app->bind(ResolveRequestPaymentGateway::class, function ($app) {
            $detector = $app->make(PaymentGatewayDetector::class);
            $alias    = $detector->detect($app->make(Request::class));
            return $this->resolveAlias($alias);
        });
    }

    /* -------------------------------------------------------------- */
    /* Helper: alias → concrete instance                               */
    /* -------------------------------------------------------------- */
    private function resolveAlias(string $alias)
    {
        return match ($alias) {
            'hyperpay' => new HyperpayGateway(
                config('payment-gateways.credentials.hyperpay.accessToken')
            ),
            'fatoorah' => new MyFatoorahGateway(
                config('payment-gateways.credentials.fatoorah.token')
            ),
            default    => throw new InvalidGatewayException("Unknown gateway [$alias]")
        };
    }
}
