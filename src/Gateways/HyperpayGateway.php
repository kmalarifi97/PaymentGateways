<?php

namespace Kmalarifi\PaymentGateways\Gateways;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Kmalarifi\PaymentGateways\Contracts\PaymentGateway;
use Kmalarifi\PaymentGateways\DTO\GatewayResponse;

/**
 * Concrete implementation for Hyperpay.
 * All heavy-lifting helpers (logging, exception mapping, etc.)
 * live in AbstractGateway so each method here is just "payload → call → normalise".
 */
class HyperpayGateway extends AbstractGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $accessToken,
        private readonly string $baseUrl = 'https://api.hyperpay.com'   // fallback; override via config
    )
    {
    }

    /* -----------------------------------------------------------------
     | 1. Create checkout
     |------------------------------------------------------------------*/
    public function createCheckout(
        float  $amount,
        string $currency,
        string $paymentMethod,
        array  $customer,
        string $merchantTransactionId
    ): GatewayResponse
    {
        $payload = [
            'amount' => (string)$amount,
            'currency' => $currency,
            'payment_method' => $paymentMethod,
            'customer_given_name' => $customer['given_name'] ?? null,
            'customer_surname' => $customer['surname'] ?? null,
            'customer_identification_doc_type' => $customer['id_doc_type'] ?? null,
            'customer_identification_doc_id' => $customer['id_doc_id'] ?? null,
            'customer_id' => $customer['id'] ?? null,
            'merchant_transaction_id' => $merchantTransactionId,
            'callback_url' => $customer['callback_url'] ?? 'route',
        ];

        $response = $this->http()->post($this->baseUrl . '/payment-gateway/v1/checkout', $payload);

        $this->guardCheckout($response);

        return $this->toGatewayResponse($response);
    }

    /* -----------------------------------------------------------------
     | 2. Check status
     |------------------------------------------------------------------*/
    public function checkStatus(string $checkoutId, string $paymentMethod): GatewayResponse
    {
        $response = $this->http()->get(
            $this->baseUrl . '/payment-gateway/v1/checkout/status',
            ['checkout_id' => $checkoutId, 'payment_method' => $paymentMethod]
        );

        return $this->toGatewayResponse($response);
    }

    /* -----------------------------------------------------------------
     | 3. Refund  (stub - fill when endpoint confirmed)
     |------------------------------------------------------------------*/
    public function refund(string $transactionId, float $amount): GatewayResponse
    {
        return new GatewayResponse(true, [
            'message' => 'Refund not implemented yet',
        ]);
    }

    /* -----------------------------------------------------------------
     | 4. Query by reference
     |------------------------------------------------------------------*/
    public function queryTransactionById(string $referenceId, string $referenceType,string $paymentMethod
    ): GatewayResponse
    {
        $params = [
            $referenceType => $referenceId,
            'payment_method' => $paymentMethod,
        ];

        $response = $this->http()->get(
            $this->baseUrl . '/payment-gateway/v1/report/query',
            $params
        );

        return $this->toGatewayResponse($response);
    }

    /* -----------------------------------------------------------------
     | 5. Query by date range
     |------------------------------------------------------------------*/
    public function queryTransactionByDate(
        Carbon $from,
        Carbon $to,
        ?int   $limit = null,
        ?int   $page = null
    ): GatewayResponse
    {
        $params = [
            'date_from' => $from->toIso8601String(),
            'date_to' => $to->toIso8601String(),
            'limit' => $limit,
            'page' => $page,
        ];

        $response = $this->http()->get(
            $this->baseUrl . '/payment-gateway/v1/report/query-range',
            array_filter($params, fn($v) => !is_null($v))
        );

        return $this->toGatewayResponse($response);
    }

    /* ================================================================
     | Helpers
     |================================================================*/
    private function http()
    {
        return Http::withToken($this->accessToken)
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
            ]);
    }

    /** Hyperpay’s success code for checkout creation */
    private function guardCheckout(Response $response): void
    {
        if (($response['result']['code'] ?? null) === '000.200.100' && isset($response['id'])) {
            return;                     // ☑️ OK, proceed
        }

        // Use the shared helper to throw consistent GatewayException
        $this->toGatewayResponse($response);   // will throw because status != 2xx OR code mismatch
    }

    /** Public helper retained from your original class */
    public static function isApprovedTransactionCode(string $code): bool
    {
        return preg_match('/^(000\.000\.|000\.100\.1|000\.[36]|000\.400\.[12]0)/', $code)
            || preg_match('/^(000\.400\.0[^3]|000\.400\.100)/', $code);
    }
}
