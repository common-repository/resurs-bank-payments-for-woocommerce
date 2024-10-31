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
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\StringInterface;
use Resursbank\Ecom\Lib\Utilities\Random;

use function trim;

/**
 * Used for indicating that a string parameter may not be empty.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringNotEmpty implements StringInterface
{
    /**
     * @throws EmptyValueException
     */
    public function validate(string $name, string $value): void
    {
        if (trim(string: $value) === '') {
            throw new EmptyValueException(
                message: 'String value ' . $name . ' cannot be empty.'
            );
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getAcceptedValues(ReflectionParameter $parameter, int $size = 5): array
    {
        $values = [];

        for ($i = 0; $i < $size; $i++) {
            $values[] = Random::getString();
        }

        return $values;
    }

    /**
     * @inheritDoc
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getRejectedValues(ReflectionParameter $parameter, int $size = 5): array
    {
        $values = [];

        for ($i = 0; $i < $size; $i++) {
            $values[] = '';
        }

        return $values;
    }
}
