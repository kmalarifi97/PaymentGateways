<?php
// src/Contracts/PaymentGatewayDetector.php
namespace Kmalarifi\PaymentGateways\Contracts;

use Illuminate\Http\Request;

interface PaymentGatewayDetector
{
    /** Primary (preferred) gateway for this request */
    public function detect(Request $request): string;

    /** Ordered list of *all* gateways the request may need */
}
