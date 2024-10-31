<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Cache;

use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Cache\Entry;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;
use Throwable;

/**
 * Basic methods utilised by all cache implementations.
 */
abstract class AbstractCache
{
    /**
     * Cache prefix, ensures our cache keys are unique.
     */
    public const CACHE_KEY_PREFIX = 'resursbank-ecom-';

    /**
     * Name of cache key containing timestamp when cache was invalidated.
     */
    public const CACHE_INVALIDATION_KEY = 'invalid';

    /**
     * Get prefixed cache key.
     */
    public static function getKey(string $key): string
    {
        return self::CACHE_KEY_PREFIX . $key;
    }

    /**
     * To ensure our keys will function regardless of cache implementation we
     * limit what characters may be utilised as part of the key. The key cannot
     * be empty.
     *
     * @throws ValidationException
     */
    public function validateKey(string $key): void
    {
        if ($key === '') {
            throw new ValidationException(
                message: 'Cache key cannot be empty.'
            );
        }

        if (preg_match(pattern: '/[^a-zA-Z\d\-_]/', subject: $key)) {
            throw new ValidationException(
                message: 'Cache key contains illegal characters.'
            );
        }

        if (!str_starts_with(haystack: $key, needle: self::CACHE_KEY_PREFIX)) {
            throw new ValidationException(
                message: 'Cache key must be prefixed with ' .
                    self::CACHE_KEY_PREFIX
            );
        }
    }

    /**
     * Create a JSON encoded cache Entry from supplied data and ttl.
     *
     * @throws JsonException
     */
    public function encodeEntry(string $data, int $ttl): string
    {
        return json_encode(
            value: new Entry(data: $data, ttl: $ttl, createdAt: time()),
            flags: JSON_THROW_ON_ERROR
        );
    }

    /**
     * Decode cache Entry from supplied JSON string, if impossible return NULL.
     */
    public function decodeEntry(string $data): ?Entry
    {
        $result = null;

        if ($data === '') {
            return null;
        }

        try {
            $obj = json_decode(
                json: $data,
                associative: false,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            );

            if ($obj instanceof stdClass) {
                $result = DataConverter::stdClassToType(
                    object: $obj,
                    type: Entry::class
                );
            }
        } catch (Throwable) {
            // Fail silently, broken cache = renew.
        }

        return $result instanceof Entry ? $result : null;
    }

    /**
     * Checks if cache entry is valid:
     *
     * 1. Cache key is the invalidation marker key, OR
     * 2. Cache entry expiration time has not been reached.
     * 3. Cache entry was created after last clearing marker.
     * 4. Cache entry is not empty.
     *
     * @throws ConfigException
     */
    public function validate(
        string $key,
        Entry $entry
    ): bool {
        $invalidKey = self::getKey(key: self::CACHE_INVALIDATION_KEY);

        if ($key === $invalidKey) {
            return true;
        }

        return
            time() < $entry->getExpirationTime() &&
            $entry->createdAt > (int) Config::getCache()->read(
                key: $invalidKey
            ) &&
            $entry->data !== ''
        ;
    }

    /**
     * Mark all cache as invalid.
     *
     * @throws ConfigException
     */
    public function invalidate(): void
    {
        // Insert invalidation marker into the cache, TTL 100 years.
        Config::getCache()->write(
            key: self::getKey(key: self::CACHE_INVALIDATION_KEY),
            data: (string) time(),
            ttl: time() + 3153600000
        );
    }
}
