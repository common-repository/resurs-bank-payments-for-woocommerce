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
use Resursbank\Ecom\Exception\Validation\IllegalUrlException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\StringInterface;
use Resursbank\Ecom\Lib\Utilities\Random;

use function preg_match;

/**
 * Used for regex validation of urls.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringIsUrl implements StringInterface
{
    /**
     * @throws IllegalUrlException
     */
    public function validate(string $name, string $value): void
    {
        if (
            !preg_match(
                pattern: '/^https?:\/\/[-a-zA-Z0-9+&@#\/%?=~_|!:,.;]*[-a-zA-Z0-9+&@#\/%=~_|]/',
                subject: $value
            )
        ) {
            throw new IllegalUrlException(
                message: $name . ' value ' . $value . ' is not a URL'
            );
        }
    }

    /**
     * @inheritDoc
     * @param ReflectionParameter $parameter
     * @param int $size
     * @return array
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getAcceptedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $urls = [];
        $characters = range(start: 'a', end: 'z');

        for ($i = 0; $i < $size; $i++) {
            $urls[] = 'https://' .
                Random::getString(length: 12, characters: $characters) . '.' .
                Random::getString(length: 3, characters: $characters);
        }

        return $urls;
    }

    /**
     * @inheritDoc
     * @param ReflectionParameter $parameter
     * @param int $size
     * @return array
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getRejectedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $urls = [];

        for ($i = 0; $i < $size; $i++) {
            $urls[] = 'hppt:/' . Random::getString(length: 24);
        }

        return $urls;
    }
}
