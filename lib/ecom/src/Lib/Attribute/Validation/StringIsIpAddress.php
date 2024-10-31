<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Exception;
use ReflectionParameter;
use Resursbank\Ecom\Exception\Validation\IllegalIpException;
use Resursbank\Ecom\Lib\Validation\StringValidation;

use function filter_var;

use const FILTER_VALIDATE_IP;

/**
 * Used for validation of IP addresses.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringIsIpAddress extends StringValidation
{
    /**
     * Validates the given IP address.
     *
     * @throws IllegalIpException
     */
    public function validate(string $name, ?string $value = null): void
    {
        if (
            $value !== null &&
            !filter_var(value: $value, filter: FILTER_VALIDATE_IP)
        ) {
            throw new IllegalIpException(
                message: $name . ' value ' . $value . ' is not a valid IP address'
            );
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function getAcceptedValues(ReflectionParameter $parameter, int $size = 5): array
    {
        return [
            '192.168.17.43',
            '2001:460:FFFF:AAAA:BBBB:CCCC:DDDD:EEEE',
            '2001:460:FFFF::999',
            'fe80::'

        ];
    }

    /**
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function getRejectedValues(ReflectionParameter $parameter, int $size = 5): array
    {
        return [
            'I.Am.Bad.IP',
            '1.2.3.4, 1.2.3.4, 1.2.3.4',
            '2001:460:FFFF:AAAA:BBBB:CCCC:DDDD:OOPS:FFFF',
            '3232238081'
        ];
    }
}
