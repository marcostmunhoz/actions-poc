<?php

namespace App\Contracts\Services;

interface PaymentGatewayInterface
{
    /**
     * @param int $amount
     *
     * @return InvoiceInterface
     */
    public function createInvoice(int $amount): InvoiceInterface;

    /**
     * @param string $identifier
     * @param array  $card
     *
     * @return bool
     */
    public function payInvoice(string $identifier, array $card): bool;
}
