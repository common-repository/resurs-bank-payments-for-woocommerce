<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use DateTime;
use Exception;
use ReflectionParameter;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\StringInterface;
use Resursbank\Ecom\Lib\Utilities\Strings;

use function preg_match;

/**
 * Used for validating ISO 8601 formatted dates.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringIsDatetime implements StringInterface
{
    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, string $value): void
    {
        $pattern = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d' .
            '|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|' .
            '[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]' .
            '\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|(' .
            '[\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';

        if (preg_match(pattern: $pattern, subject: $value)) {
            return;
        }

        throw new IllegalValueException(
            message: $name . ' value ' . $value . ' is not a valid date'
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getAcceptedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $values = [];

        for ($i = 0; $i < $size; $i++) {
            $timestamp = (string)mt_rand(
                min: (int)(new DateTime(datetime: '1970-01-01 00:00:00'))
                    ->format('U'),
                max: (int)(new DateTime(datetime: '2100-12-31 23:59:59'))
                    ->format('U')
            );

            $date = DateTime::createFromFormat(
                format: 'U',
                datetime: $timestamp
            );

            if (!($date instanceof DateTime)) {
                continue;
            }

            $values[] = $date->format(format: 'c');
        }

        return $values;
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getRejectedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $values = [];

        for ($i = 0; $i < $size; $i++) {
            $values[] = Strings::generateRandomString(
                length: mt_rand(min: 3, max: 30),
                characters: 'abcdefghijklmnopqrstuvxyz"#()!/€'
            );
        }

        return $values;
    }
}
