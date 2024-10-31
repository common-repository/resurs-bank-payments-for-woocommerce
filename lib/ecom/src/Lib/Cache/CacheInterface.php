<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Cache;

/**
 * Describes methods required for a cache storage driver.
 */
interface CacheInterface
{
    /**
     * Read value from cache. NULL means there was no valid value
     */
    public function read(string $key): ?string;

    /**
     * Write value to cache.
     *
     * @param int $ttl | Timeout in seconds before cache becomes stale (expire).
     */
    public function write(string $key, string $data, int $ttl): void;

    /**
     * Clear value from cache manually.
     */
    public function clear(string $key): void;

    /**
     * Validate key.
     */
    public function validateKey(string $key): void;

    /**
     * Mark all existing cache as invalid.
     */
    public function invalidate(): void;
}
