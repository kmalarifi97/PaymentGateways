<?php
namespace Kmalarifi\PaymentGateways\Contracts;

use Carbon\Carbon;
use Kmalarifi\PaymentGateways\DTO\GatewayResponse;

interface PaymentGateway
{
    public function createCheckout(
        float  $amount,
        string $currency,
        string $paymentMethod,
        array  $customer,
        string $merchantTransactionId
    ): GatewayResponse;

    public function checkStatus(string $checkoutId, string $paymentMethod): GatewayResponse;

    public function refund(string $transactionId, float $amount): GatewayResponse;

    public function queryTransactionById(
        string $referenceId,
        string $referenceType,
        string $paymentMethod
    ): GatewayResponse;

    public function queryTransactionByDate(
        Carbon $from,
        Carbon $to,
        ?int $limit = null,
        ?int $page  = null
    ): GatewayResponse;
}
