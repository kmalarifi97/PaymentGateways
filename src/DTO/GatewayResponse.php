<?php

// src/DTO/GatewayResponse.php
namespace Kmalarifi\PaymentGateways\DTO;

class GatewayResponse
{
    public function __construct(
        public readonly bool   $ok,
        public readonly array  $data = [],
        public readonly ?array $meta = null

    ) {}
}
