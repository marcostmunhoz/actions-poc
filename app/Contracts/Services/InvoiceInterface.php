<?php

namespace App\Contracts\Services;

interface InvoiceInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @return int
     */
    public function getAmount(): int;
}
