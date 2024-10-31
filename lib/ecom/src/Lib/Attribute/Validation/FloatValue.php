<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation;

use Attribute;
use Exception;
use Random\RandomException;
use ReflectionParameter;
use Resursbank\Ecom\Exception\AttributeParameterException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\FloatInterface;

/**
 * Used for setting minimum and maximum value on float properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class FloatValue implements FloatInterface
{
    /**
     * NOTE: min and max are nullable to allow for no min / max values, because
     * you may simply wish to confirm the count of decimals in a float value.
     *
     * @param int $scale Maximum number of decimals allowed in float value.
     * @throws AttributeParameterException
     */
    public function __construct(
        public readonly ?float $min = null,
        public readonly ?float $max = null,
        public readonly int $scale = 2
    ) {
        if ($min !== null && $max !== null && $min > $max) {
            throw new AttributeParameterException(
                message: 'Attribute min parameter value (' .
                $min . ') is greater than max parameter value (' . $max . ')!'
            );
        }

        if ($this->scale < 1) {
            throw new AttributeParameterException(
                message: 'Attribute scale parameter value (' .
                $this->scale . ') is less than 1!'
            );
        }
    }

    /**
     * @throws IllegalValueException
     */
    public function validate(string $name, float $value): void
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

        // Confirm value contains no more decimals than allowed by specified scale.
        if ($this->getNumberOfDecimals($value) > $this->scale) {
            throw new IllegalValueException(
                message: 'Value of ' . $name . ' contains more decimals than specified scale of ' .
                    $this->scale
            );
        }
    }

    /**
     * Get number of decimals in float value.
     */
    public function getNumberOfDecimals(float $value): int
    {
        if (!str_contains((string) $value, '.')) {
            return 0;
        }

        return strlen(explode('.', (string) $value)[1]);
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

        // Add random values.
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->getRandomValue();
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
            $result[] = $this->min - 0.01;
        }

        if ($this->max !== null) {
            $result[] = $this->max + 0.01;
        }

        if ($this->min === null && $this->max === null) {
            return $result;
        }

        $this->addRandomRejectedValues(result: $result, size: $size);

        return $result;
    }

    /**
     * Resolve random float value between min and max.
     *
     * @throws RandomException
     */
    public function getRandomValue(
        ?float $min = null,
        ?float $max = null,
        ?int $scale = null
    ): float {
        if ($max === null) {
            $max = $this->max ?? 999999999 + $this->getRandomDecimal();
        }

        if ($min === null) {
            $min = $this->min ?? -999999999 + $this->getRandomDecimal();
        }

        if ($min > $max) {
            $max = $min + 9999;
        }

        $result = random_int(
            (int) $min,
            (int) $max
        ) + $this->getRandomDecimal();

        if ($result > $max) {
            $result = $max;
        }

        if ($result < $min) {
            $result = $min;
        }

        return round($result, $scale ?? $this->scale);
    }

    /**
     * Generate a value between 0.0 and 0.999999999999 (depending on scale).
     */
    public function getRandomDecimal(): float
    {
        $result = round(mt_rand() / mt_getrandmax(), $this->scale);

        $min = $this->getDecimalValue((float) $this->min);
        $max = $this->getDecimalValue((float) $this->max);

        return match (true) {
            $result < $min => (float) "0.$min",
            $result > $max => (float) "0.$max",
            default => $result,
        };
    }

    /**
     * Get decimal (scale) value from float.
     */
    public function getDecimalValue(float $value): int
    {
        if (str_contains((string) $value, '.')) {
            return (int) explode('.', (string) $value)[1];
        }

        return 0;
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
            // Generate values above max.
            if ($this->max !== null) {
                $result[] = $this->getRandomValue(
                    min: $this->max + 1,
                    max: $this->max + 999999
                );
                $count++;
            }

            // Generate values below min.
            if ($this->min === null) {
                continue;
            }

            $result[] = $this->getRandomValue(
                min: $this->min - 999999,
                max: $this->min - 1
            );
            $count++;
        }
    }
}
