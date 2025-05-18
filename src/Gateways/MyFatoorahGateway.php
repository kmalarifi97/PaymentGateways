<?php

namespace Kmalarifi\PaymentGateways\Gateways;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Kmalarifi\PaymentGateways\Contracts\PaymentGateway;
use Kmalarifi\PaymentGateways\DTO\GatewayResponse;


class MyFatoorahGateway extends AbstractGateway implements PaymentGateway
{
    public function __construct(private readonly string $token)
    {
        $this->host  = config('payment-gateways.hosts.fatoorah');
        $this->path  = config('payment-gateways.paths.fatoorah');
    }

    /* --------------------------------- 1. Checkout */
    public function createCheckout(
        float  $amount,
        string $currency,
        string $paymentMethod,
        array  $customer,
        string $merchantTransactionId
    ): GatewayResponse {

        $response = $this->http()->post($this->url('initiate'), ['InvoiceAmount' => $amount, 'CurrencyIso' => $currency]
        );

        $methodId = collect($response['Data']['PaymentMethods'] ?? [])
            ->firstWhere('PaymentMethodEn', $paymentMethod)['PaymentMethodId'] ?? null;

        if (! $methodId) {
            return new GatewayResponse(false, [], ['reason' => 'Payment method not found']);
        }

        $payload = [
            'PaymentMethodId'   => $methodId,
            'CustomerName'      => trim(($customer['given_name'] ?? '').' '.($customer['surname'] ?? '')),
            'DisplayCurrencyIso'=> $currency,
            'InvoiceValue'      => $amount,
            'CustomerEmail'     => $customer['email']        ?? '',
            'CallBackUrl'       => $customer['callback_url'] ?? url('/myfatoorah/callback'),
            'ErrorUrl'          => $customer['error_url']    ?? url('/myfatoorah/error'),
            'CustomerReference' => $merchantTransactionId,
        ];

        $execute = $this->http()->post($this->url('execute'), $payload);

        return $this->toGatewayResponse($execute);
    }

    /* --------------------------------- 2. Status  */
    public function checkStatus(string $paymentId, string $unused = ''): GatewayResponse
    {
        $response = $this->http()->post($this->url('status'), [
            'KeyType' => 'PaymentId',
            'Key'     => $paymentId,
        ]);

        return $this->toGatewayResponse($response);
    }

    /* ------- stubs for refund / queries remain unchanged ------- */
    public function refund(string $transactionId, float $amount): GatewayResponse
    {
        return new GatewayResponse(true, ['message' => 'Refund not implemented yet']);
    }

    public function queryTransactionById(
        string $referenceId, string $referenceType, string $paymentMethod
    ): GatewayResponse {
        return $this->checkStatus($referenceId, '');
    }

    public function queryTransactionByDate(
        Carbon $from, Carbon $to, ?int $limit = null, ?int $page = null
    ): GatewayResponse {
        return new GatewayResponse(false, [], ['reason' => 'Endpoint not provided by MyFatoorah']);
    }

    /* ------------------- helpers ------------------ */
    private function http()
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->withHeaders([
                'Content-Type'    => 'application/json',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection'      => 'keep-alive',
            ]);
    }

    private function url(string $key): string
    {
        return $this->host . ($this->path[$key] ?? '');
    }

    private string $host;
    private array  $path;
}
