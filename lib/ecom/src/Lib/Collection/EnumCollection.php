<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Collection;

use BackedEnum;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use ValueError;

use function in_array;
use function is_string;

/**
 * Base collection class.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class EnumCollection extends Collection
{
    /**
     * @throws ValueError
     * @throws IllegalTypeException
     */
    public function __construct(array $data, string $type)
    {
        parent::__construct(
            data: $this->evaluateData(data: $data, type: $type),
            type: $type
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws ValueError
     * @todo Refactor ECP-593
     */
    // phpcs:ignore
    private function evaluateData(array $data, string $type): array
    {
        if (!is_subclass_of(object_or_class: $type, class: BackedEnum::class)) {
            throw new IllegalTypeException(
                message: 'This collection only accepts backed enums.'
            );
        }

        $newData = [];

        foreach ($data as $v) {
            if (
                in_array(
                    needle: $v,
                    haystack: $type::cases(),
                    strict: true
                )
            ) {
                $newData[] = $v;
                continue;
            }

            if (is_string(value: $v)) {
                $newData[] = $type::from(value: $v);
                continue;
            }

            throw new IllegalTypeException(
                message: "Only strings, or cases from $type, are acceptable."
            );
        }

        return $newData;
    }
}
