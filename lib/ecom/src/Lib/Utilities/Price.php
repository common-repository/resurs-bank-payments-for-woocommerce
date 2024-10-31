<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;

/**
 * Methods to relating to price operations.
 */
class Price
{
    /**
     * Flexible price formatter
     *
     * @throws ConfigException
     */
    public static function format(
        int|float $value,
        int $decimals = 2,
        string $decimalSeparator = ',',
        string $thousandsSeparator = ' '
    ): string {
        $formattedAmount = number_format(
            num: $value,
            decimals: $decimals,
            decimal_separator: $decimalSeparator,
            thousands_separator: $thousandsSeparator
        );

        return Config::getCurrencyFormat() === CurrencyFormat::SYMBOL_FIRST ?
            Config::getCurrencySymbol() . $formattedAmount :
            $formattedAmount . ' ' . Config::getCurrencySymbol();
    }
}
