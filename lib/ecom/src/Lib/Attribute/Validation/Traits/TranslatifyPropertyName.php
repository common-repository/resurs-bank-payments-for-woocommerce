<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation\Traits;

/**
 * Converts model property names into translation id strings.
 */
trait TranslatifyPropertyName
{
    /**
     * Performs the actual conversion.
     *
     * @param string $propertyName Normally formatted property name.
     * @return string String which can be used as a translation identifier.
     */
    public static function convert(string $propertyName): string
    {
        return strtolower(string: (string)preg_replace(
            pattern: '/([A-Z])/',
            replacement: '-$1',
            subject: $propertyName
        ));
    }
}
