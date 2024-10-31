<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

/**
 * Tax related methods.
 */
class Tax
{
    /**
     * Calculate tax rate based on supplied values.
     *
     * @todo Should return Enum since API only accepts specific values. ECP-308
     */
    public static function getRate(float $taxAmount, float $totalInclTax): float
    {
        return $taxAmount === 0.0 || $totalInclTax === 0.0 ? 0.0 : round(
            num: $taxAmount / $totalInclTax,
            precision: 2
        ) * 100;
    }
}
