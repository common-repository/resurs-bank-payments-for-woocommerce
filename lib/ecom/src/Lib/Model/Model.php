<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use BackedEnum;
use JsonException;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\ArrayOfStrings;
use Resursbank\Ecom\Lib\Attribute\Validation\ArraySize;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\ArrayInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\CollectionInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\FloatInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\IntInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\Interface\StringInterface;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsIpAddress;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Collection\Collection;

use function is_array;
use function is_object;

/**
 * Defines the basic structure of an Ecom model.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Model
{
    /**
     * List of valid validation attribute combinations.
     *
     * @var array<array>
     */
    private static array $attributeCombos = [
        [
            ArraySize::class,
            ArrayOfStrings::class
        ]
    ];

    /**
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws JsonException
     */
    public function __construct()
    {
        $this->validateProperties();
    }

    /**
     * Get attributes utilised for validation attached to Model property.
     *
     * @throws JsonException
     * @throws AttributeCombinationException
     */
    public static function getValidationAttributes(
        ReflectionParameter $parameter
    ): array {
        $result = [];
        $combo = [];

        foreach ($parameter->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            if (!self::isValidationAttribute(attribute: $instance)) {
                continue;
            }

            $result[] = $instance;
            $combo[] = $instance::class;
        }

        if (count($result) === 2) {
            self::validateAttributeCombination(
                parameter: $parameter,
                combo: $combo
            );
        }

        return $result;
    }

    /**
     * Check whether supplied $attribute is part of validation suite.
     */
    public static function isValidationAttribute(
        object $attribute
    ): bool {
        return
            // Adding this because I'm just trying to fix the tests and don't want to get bogged down in writing the
            // methods for test data.
            $attribute instanceof StringMatchesRegex ||
            $attribute instanceof StringIsIpAddress ||
            $attribute instanceof StringInterface ||
            $attribute instanceof IntInterface ||
            $attribute instanceof FloatInterface ||
            $attribute instanceof CollectionInterface ||
            $attribute instanceof ArrayInterface
        ;
    }

    /**
     * Confirm combination of validation attributes is functional.
     *
     * A combination of validation attributes requires one of the attributes to
     * implement business logic for producing testable data matching said
     * combination. Without that, automated tests cannot be safely conducted
     * and as such we will reject any combination we do not explicitly allow.
     *
     * @throws AttributeCombinationException
     * @throws JsonException
     */
    public static function validateAttributeCombination(
        ReflectionParameter $parameter,
        array $combo
    ): void {
        $combo = sort($combo);
        $validCombo = false;

        foreach (self::$attributeCombos as $c) {
            $validCombo = (sort($c) === $combo);
        }

        if (!$validCombo) {
            throw new AttributeCombinationException(
                message: sprintf(
                    'Cannot combines %s attributes for parameter %s on %s',
                    json_encode(value: $combo, flags: JSON_THROW_ON_ERROR),
                    $parameter->name,
                    $parameter->getDeclaringClass()?->name
                )
            );
        }
    }

    /**
     * Converts the object to an array suitable for use with the Curl library.
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @todo Refactor see ECP-354. Remove phpcs:ignore when done.
     */
    // phpcs:ignore
    public function toArray(
        bool $full = false,
        ?array $raw = null
    ): array {
        $data = [];

        $raw ??= get_object_vars(object: $this);

        foreach ($raw as $name => $value) {
            if (is_object(value: $value)) {
                // Skip DI.
                if ($value instanceof Collection || $value instanceof self) {
                    $data[$name] = $value->toArray(full: $full);
                }

                if ($value instanceof BackedEnum) {
                    $data[$name] = $value->value;
                }
            } elseif (is_array(value: $value)) {
                // Support arrays containing Model|Collection.
                $data[$name] = $this->toArray(full: $full, raw: $value);
            } else {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    /**
     * Validate object properties.
     *
     * @throws ReflectionException
     * @throws JsonException
     * @throws AttributeCombinationException
     */
    private function validateProperties(): void
    {
        $parameters = (new ReflectionMethod(
            objectOrMethod: $this,
            method: '__construct'
        ))->getParameters();

        foreach ($parameters as $parameter) {
            $this->validateProperty(parameter: $parameter);
        }
    }

    /**
     * Validate individual parameter.
     *
     * @throws JsonException
     * @throws AttributeCombinationException
     */
    private function validateProperty(ReflectionParameter $parameter): void
    {
        if ($this->{$parameter->name} === null && $parameter->allowsNull()) {
            return;
        }

        foreach (
            self::getValidationAttributes(parameter: $parameter) as $attribute
        ) {
            $attribute->validate(
                name: $parameter->name,
                value: $this->{$parameter->name}
            );
        }
    }
}
