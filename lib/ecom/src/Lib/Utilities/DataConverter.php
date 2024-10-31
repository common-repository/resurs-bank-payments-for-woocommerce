<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use ArgumentCountError;
use BackedEnum;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionObject;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Collection\EnumCollection;
use Resursbank\Ecom\Lib\Model\Model;
use stdClass;

use function call_user_func;
use function is_object;
use function is_string;

/**
 * Utility class for data type conversions.
 */
class DataConverter
{
    /**
     * Converts stdClass objects to specified type.
     *
     * NOTE: The intention is that the conversion class itself validates
     * assigned values through its constructor.
     *
     * @param class-string $type
     * @throws ReflectionException
     * @throws ArgumentCountError
     * @throws IllegalTypeException|IllegalValueException
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @todo Refactor ECP-353
     */
    // phpcs:ignore
    public static function stdClassToType(object $object, string $type): Model
    {
        if (!is_subclass_of(object_or_class: $type, class: Model::class)) {
            throw new IllegalValueException(message: "$type is not a Model");
        }

        $sourceReflection = new ReflectionObject(object: $object);
        $destReflection = new ReflectionClass(objectOrClass: $type);
        $sourceProperties = $sourceReflection->getProperties();
        $arguments = [];

        foreach ($sourceProperties as $sourceProperty) {
            /** @noinspection PhpExpressionResultUnusedInspection */
            $sourceProperty->setAccessible(accessible: true);
            $name = $sourceProperty->getName();
            $value = $sourceProperty->getValue(object: $object);

            if (!$destReflection->hasProperty(name: $name)) {
                continue;
            }

            $destinationProperty = $destReflection->getProperty(name: $name);
            /** @var ReflectionNamedType $destinationType */
            $destinationType = $destinationProperty->getType();
            $propertyType = $destinationType->getName();

            // If our property is a collection we need to take the value array and convert all items individually
            // before loading our new collection object
            if (
                is_subclass_of(
                    object_or_class: $propertyType,
                    class: Collection::class
                )
            ) {
                $converted = [];
                $dummyCollection = new $propertyType(data: []);
                $dummyCollectionType = $dummyCollection->getType();

                if (is_iterable(value: $value)) {
                    foreach ($value as $item) {
                        if (
                            is_string(value: $item) &&
                            is_subclass_of(
                                object_or_class: $propertyType,
                                class: EnumCollection::class
                            )
                        ) {
                            $converted[] = $dummyCollectionType::from($item);
                        } else {
                            $converted[] = self::stdClassToType(
                                object: $item,
                                type: $dummyCollectionType
                            );
                        }
                    }
                }

                $dummyCollection->setData(data: $converted);
                $arguments[$name] = $dummyCollection;
            } elseif (
                $propertyType === 'array' &&
                $value instanceof stdClass &&
                empty((array)$value)
            ) {
                $arguments[$name] = [];
            } elseif (enum_exists(enum: $propertyType)) {
                // If our property is an enum we need to convert the value
                // to the enum value it represents.
                // @todo enum_exists guarantees UnitEnum, we expect BackedEnum. See ECP-339
                $arguments[$name] = $value !== null ?
                    call_user_func(
                    /* @phpstan-ignore-next-line */
                        $propertyType . '::from',
                        $value instanceof BackedEnum ? $value->value : $value
                    ) : null;
            } elseif (is_object(value: $value)) {
                $arguments[$name] = self::stdClassToType(
                    object: $value,
                    /* @phpstan-ignore-next-line */
                    type: $propertyType
                );
            } else {
                $arguments[$name] = $value;
            }
        }

        return new $type(...$arguments);
    }

    /**
     * @param class-string $type
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     */
    public static function arrayToCollection(array $data, string $type): Collection
    {
        if (!is_subclass_of(object_or_class: $type, class: Model::class)) {
            throw new IllegalValueException(message: "$type is not a Model");
        }

        $convertedData = [];

        foreach ($data as $item) {
            $convertedData[] = self::stdClassToType(object: $item, type: $type);
        }

        $class = $type . 'Collection';

        if (
            !is_subclass_of(object_or_class: $class, class: Collection::class)
        ) {
            throw new IllegalValueException(
                message: "$type is not a Collection"
            );
        }

        return new $class(data: $convertedData);
    }
}
