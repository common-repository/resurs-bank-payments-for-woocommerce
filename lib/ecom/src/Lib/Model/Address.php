<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use JsonException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Order\CountryCode;

/**
 * Address information block about a payment.
 */
class Address extends Model
{
    /**
     * @throws JsonException
     * @throws \ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringLength(min: 1, max: 100)] public readonly string $addressRow1,
        #[StringLength(min: 1, max: 50)] public readonly string $postalArea,
        #[StringMatchesRegex(pattern: '/^[ \d]{1,10}$/')]
        public readonly string $postalCode,
        public readonly ?CountryCode $countryCode = null,
        #[StringLength(max: 50)] public readonly ?string $fullName = null,
        #[StringLength(max: 50)] public readonly ?string $firstName = null,
        #[StringLength(max: 50)] public readonly ?string $lastName = null,
        #[StringLength(max: 100)] public readonly ?string $addressRow2 = null
    ) {
        parent::__construct();
    }
}
