<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Cache;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Cache entry model.
 */
class Entry extends Model
{
    /**
     * Assign properties.
     */
    public function __construct(
        public readonly string $data,
        public readonly int $ttl,
        public readonly int $createdAt
    ) {
    }

    /**
     * Returns when cache entry expires.
     */
    public function getExpirationTime(): int
    {
        return $this->createdAt + $this->ttl;
    }
}
