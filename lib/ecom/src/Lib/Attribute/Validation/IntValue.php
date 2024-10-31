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
use Resursbank\Ecom\Exception\AttributeParameterException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\IntInterface;

/**
 * Used for setting minimum and maximum value on int properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class IntValue implements IntInterface
{
    /**
     * @param int|null $min Minimum value
     * @param int|null $max Maximum value
     * @throws AttributeParameterException
     */
    public function __construct(
        public readonly ?int $min = null,
        public readonly ?int $max = null
    ) {
        if ($min === null && $max === null) {
            throw new AttributeParameterException(
                message: 'Attribute min and max parameters cannot both be null!'
            );
        }

        if ($min !== null && $max !== null && $min > $max) {
            throw new AttributeParameterException(
                message: 'Attribute min parameter value (' .
                $min . ') is greater than max parameter value (' . $max . ')!'
            );
        }
    }

    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, int $value): void
    {
        if (isset($this->min) && $value < $this->min) {
            throw new IllegalValueException(
                message: 'Value of ' . $name . ' is less than its specified minimum value of ' . $this->min
            );
        }

        if (isset($this->max) && $value > $this->max) {
            throw new IllegalValueException(
                message: 'Value of ' . $name . ' is greater than its specified maximum value of ' . $this->max
            );
        }
    }

    /**
     * @inheritdoc
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getAcceptedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $size = max($size, 1);

        $result = [];

        // Add threshold values.
        if ($this->min !== null) {
            $result[] = $this->min;
        }

        if ($this->max !== null) {
            $result[] = $this->max;
        }

        /* Resolve default values to generate random values (no min / max = all
           values are allowed). */
        $min = ($this->min ?? -999999999);
        $max = ($this->max ?? 999999999);

        // Add random values.
        for ($i = 0; $i < $size; $i++) {
            $result[] = random_int(min: $min, max: $max);
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @throws Exception
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getRejectedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array {
        $size = max($size, 2);

        $result = [];

        // Add threshold values.
        if ($this->min !== null) {
            $result[] = $this->min - 1;
        }

        if ($this->max !== null) {
            $result[] = $this->max + 1;
        }

        if ($this->min === null && $this->max === null) {
            return $result;
        }

        $this->addRandomRejectedValues(result: $result, size: $size);

        return $result;
    }

    /**
     * Append randomized values which will be rejected by property validation.
     *
     * Note: cannot generate rejected random values without min / max (no min
     * / max = all values are allowed).
     *
     * @throws Exception
     */
    private function addRandomRejectedValues(
        array &$result,
        int $size
    ): void {
        $count = 0;

        // Generate random values.
        while ($count < $size) {
            if ($this->max !== null) {
                $result[] = random_int(
                    min: $this->max + 1,
                    max: $this->max + 999999
                );
                $count++;
            }

            if ($this->min === null) {
                continue;
            }

            $result[] = random_int(
                min: $this->min - 999999,
                max: $this->min - 1
            );
            $count++;
        }
    }
}
