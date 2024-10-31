<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Exception;

use Exception;
use Resursbank\Ecom\Lib\Attribute\Validation\Traits\TranslatifyPropertyName;
use Resursbank\Ecom\Lib\Locale\Translator;
use Throwable;

/**
 * Specifies a problem when validating a property.
 */
class ValidationException extends Exception
{
    use TranslatifyPropertyName;

    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        public ?string $friendlyMessage = null
    ) {
        parent::__construct(
            message: $message,
            code: $code,
            previous: $previous
        );
    }

    /**
     * Render a friendly message.
     *
     * @param string $propertyName Model property name
     * @param string $errorId ID of error message to use
     * @return string|null Human-readable friendly message or null
     */
    public static function getFriendlyMessage(
        string $propertyName,
        string $errorId
    ): ?string {
        try {
            return str_replace(
                search: '%1',
                replace: Translator::translate(
                    phraseId: self::convert(propertyName: $propertyName)
                ),
                subject: Translator::translate(
                    phraseId: $errorId
                )
            );
        } catch (Throwable) {
            return null;
        }
    }
}
