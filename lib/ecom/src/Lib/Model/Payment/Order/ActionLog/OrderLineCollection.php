<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Defines order line (product) collection.
 */
class OrderLineCollection extends Collection
{
    /**
     * @param array<int, OrderLine> $data
     * @throws IllegalTypeException
     */
    public function __construct(
        #[ArraySize(min: 1, max: 1000)] array $data
    ) {
        parent::__construct(data: $data, type: OrderLine::class);
    }

    /**
     * Resolve total amount.
     */
    public function getTotal(): float
    {
        $amount = 0.0;

        /** @var OrderLine $orderLine */
        foreach ($this->getData() as $orderLine) {
            $amount += $orderLine->totalAmountIncludingVat;
        }

        return round(num: $amount, precision: 2);
    }
}
