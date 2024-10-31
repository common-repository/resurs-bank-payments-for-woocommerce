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
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\ArrayInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\Traits\ArrayValidation;
use Resursbank\Ecom\Lib\Utilities\Random\DataType;

use function is_string;

/**
 * Used for validating that an array only contains strings.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ArrayOfStrings implements ArrayInterface
{
    use ArrayValidation;

    /**
     * @throws IllegalTypeException
     */
    public function validate(string $name, array $value): void
    {
        foreach ($value as $key => $element) {
            if (!is_string(value: $element)) {
                throw new IllegalTypeException(
                    message: 'Array ' . $name . ' contains data that is not ' .
                        'of type string at index ' . $key . '.'
                );
            }
        }
    }

    /**
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getAcceptedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $result = [];

        // Extract min / max size from combined ArraySize attribute.
        $min = $this->getSizeAttribute(parameter: $parameter)?->min;
        $max = $this->getSizeAttribute(parameter: $parameter)?->max;

        // Add threshold values.
        if ((int) $min > 0) {
            $this->getRandom(
                min: (int) $min,
                max: (int) $min,
                type: DataType::STRING
            );
        } else {
            $result[] = [];
        }

        if ($max !== null) {
            $this->getRandom(min: $max, max: $max, type: DataType::STRING);
        }

        // Add randomized values.
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->getRandom(
                min: $min ?? 0,
                max: $max ?? 100,
                type: DataType::STRING
            );
        }

        return $result;
    }

    /**
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getRejectedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $result = [];

        // Extract min / max size from combined ArraySize attribute.
        $min = $this->getSizeAttribute(parameter: $parameter)?->min;
        $max = $this->getSizeAttribute(parameter: $parameter)?->max;

        // Add threshold values.
        if ($min !== null) {
            $this->getRandom(min: $min - 1, max: $min - 1);
        } else {
            $result[] = [];
        }

        if ($max !== null) {
            $this->getRandom(min: $max + 1, max: $max + 1);
        }

        // Add randomized values.
        $this->addRandomRejectedValues(
            result: $result,
            size: $size,
            min: (int) $min,
            max: $max,
            type: DataType::STRING
        );

        return $result;
    }

    /**
     * Resolve attribute ArraySize from ReflectionParameter if defined.
     */
    public function getSizeAttribute(
        ReflectionParameter $parameter
    ): ?ArraySize {
        $attributes = $parameter->getAttributes(name: ArraySize::class);

        return isset($attributes[0]) ? $attributes[0]->newInstance() : null;
    }
}
