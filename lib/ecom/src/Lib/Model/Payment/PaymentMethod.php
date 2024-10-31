<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines a payment method object returned when fetching a payment
 */
class PaymentMethod extends Model
{
    /**
     * @throws EmptyValueException
     */
    public function __construct(
        #[StringNotEmpty] public readonly string $name
    ) {
        parent::__construct();
    }
}
