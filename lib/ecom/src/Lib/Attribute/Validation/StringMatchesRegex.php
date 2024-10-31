<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Traits\TranslatifyPropertyName;

use function preg_match;

/**
 * Used for regex validation of strings.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringMatchesRegex
{
    use TranslatifyPropertyName;

    /**
     * @param string $pattern Regex pattern the property value has to match.
     */
    public function __construct(
        private readonly string $pattern
    ) {
    }

    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, string $value): void
    {
        if (!preg_match(pattern: $this->pattern, subject: $value)) {
            throw new IllegalValueException(
                message: $name . ' value ' . $value . ' does not match ' .
                    $this->pattern,
                friendlyMessage: IllegalValueException::getFriendlyMessage(
                    propertyName: $name,
                    errorId: 'field-has-has-invalid-value'
                )
            );
        }
    }
}
