<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Collection;

use ArrayAccess;
use Countable;
use Iterator;
use Resursbank\Ecom\Exception\CollectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Model;

use function is_object;

/**
 * Base collection class.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Collection implements ArrayAccess, Iterator, Countable
{
    private const TYPE_ERR = 'Collection requires data to be of type %s, received %s';
    private const TYPE_ERR_NO_DATA = 'No type or data specified';

    protected string $type;

    private int $position;

    /**
     * @throws IllegalTypeException
     */
    public function __construct(private array $data, ?string $type = null)
    {
        $type = $this->determineType(data: $data, type: $type);
        $this->verifyDataArrayType(data: $data, type: $type);
        $this->type = $type;
        $this->position = 0;
    }

    /**
     * Check if property with value exists.
     *
     * @param string $propertyName Name of property to search for
     * @param mixed $propertyValue Value to search for
     */
    public function hasObjectWithPropertyValue(
        string $propertyName,
        mixed $propertyValue
    ): bool {
        foreach ($this->data as $object) {
            if (
                is_object(value: $object) &&
                property_exists(
                    object_or_class: $object,
                    property: $propertyName
                ) &&
                $object->$propertyName === $propertyValue
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter the collection based on exact property value.
     *
     * This method both returns an instance of the collection and alters the
     * existing instance. It also resets the array indexing of the collection's
     * data property.
     *
     * @param string $property Property to filter on.
     * @param mixed $value Property value to match.
     * @return $this Updated collection with all non-matching objects removed.
     * @throws CollectionException
     */
    public function filterByPropertyValue(
        string $property,
        mixed $value
    ): Collection {
        if (
            !property_exists(object_or_class: $this->type, property: $property)
        ) {
            throw new CollectionException(
                message: 'Filter value has to be of type ' .
                getType($this->$property) . ', received ' . getType($value)
            );
        }

        $this->data = array_values(array: array_filter(
            array: $this->data,
            callback: static fn ($object): bool => $object->$property === $value
        ));

        $this->rewind();

        return $this;
    }

    /**
     * Set new data array
     *
     * @throws IllegalTypeException
     */
    public function setData(array $data): void
    {
        $this->verifyDataArrayType(data: $data, type: $this->type);

        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Get collection type
     *
     * @return class-string
     */
    public function getType(): string
    {
        /* @phpstan-ignore-next-line */
        return $this->type;
    }

    /**
     * Get data array from collection
     *
     * @param bool $full Expand all child objects
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function toArray(
        bool $full = false
    ): array {
        $data = $full ? [] : $this->data;

        if ($full) {
            $data = $this->fullToArray();
        }

        return $data;
    }

    /**
     * Get full data array from collection.
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function fullToArray(): array
    {
        $data = [];

        /** @var Model $model */
        foreach ($this->data as $model) {
            if (method_exists(object_or_class: $model, method: 'toArray')) {
                $data[] = $model->toArray(full: true);
            } else {
                $data[] = $model;
            }
        }

        return $data;
    }

    /**
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @todo Refactor, too complex. See ECP-346
     */
    // phpcs:ignore
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (
            (
                is_object(value: $value) &&
                $value::class !== $this->type
            ) ||
            (
                !is_object(value: $value) &&
                gettype(value: $value) !== $this->type
            )
        ) {
            throw new IllegalTypeException(
                message: sprintf(
                    self::TYPE_ERR,
                    $this->type,
                    is_object(value: $value) ? $value::class : gettype(
                        value: $value
                    )
                )
            );
        }

        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!isset($this->data[$offset])) {
            $this->data[$offset] = null;
        }

        return $this->data[$offset];
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @throws CollectionException
     */
    public function current(): mixed
    {
        if (!isset($this->data[$this->position])) {
            throw new CollectionException(
                message: 'Could not find any data in data array.'
            );
        }

        return $this->data[$this->position];
    }

    /**
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function key(): mixed
    {
        // NOTE: Parent method returns mixed, so we cannot specify int.
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }

    /**
     * Get collection from specified type or first element of data array
     *
     * @throws IllegalTypeException
     */
    private function determineType(array $data, ?string $type = null): string
    {
        if ($type) {
            return $type;
        }

        if (!empty($data) && isset($data[0])) {
            return is_object(value: $data[0]) ? $data[0]::class : gettype(
                value: $data[0]
            );
        }

        throw new IllegalTypeException(message: self::TYPE_ERR_NO_DATA);
    }

    /**
     * Verify the type of objects in collection data
     *
     * @throws IllegalTypeException
     * @todo Refactor, too complex, see ECP-347
     */
    // phpcs:ignore
    private function verifyDataArrayType(array $data, string $type): void
    {
        foreach ($data as $item) {
            if (
                (
                    is_object(value: $item) &&
                    $item::class !== $type
                ) ||
                (
                    !is_object(value: $item) &&
                    gettype(value: $item) !== $type
                )
            ) {
                throw new IllegalTypeException(
                    message: sprintf(
                        self::TYPE_ERR,
                        $type,
                        (is_object(value: $item) ? $item::class : gettype(
                            value: $item
                        ))
                    )
                );
            }
        }
    }
}
