<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUrl;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Order\PaymentMethod\LegalLink\Type;

/**
 * Defines a legal info link.
 */
class LegalLink extends Model
{
    /**
     * @throws EmptyValueException
     */
    public function __construct(
        #[StringIsUrl] public readonly string $url,
        public readonly Type $type,
        public readonly bool $appendAmount
    ) {
        parent::__construct();
    }
}
