<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Store;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Defines a Store collection.
 */
class StoreCollection extends Collection
{
    /**
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: Store::class);
    }

    /**
     * Convert collection data to assoc array prepared for select elements.
     */
    public function getSelectList(): array
    {
        $result = [];

        /** @var Store $store */
        foreach ($this->getData() as $store) {
            $result[$store->id] = "$store->nationalStoreId: $store->name";
        }

        return $result;
    }

    /**
     * Find and return store by ID.
     */
    public function filterById(string $id): ?Store
    {
        $filtered = array_filter(
            $this->getData(),
            static fn (Store $store) => $store->id === $id
        );

        return count($filtered) === 1 ? array_values($filtered)[0] : null;
    }

    /**
     * Resolve ID value of only available store.
     */
    public function getSingleStoreId(): ?string
    {
        return count($this->getData()) === 1 ?
            array_values(array: $this->getData())[0]->id : null;
    }
}
