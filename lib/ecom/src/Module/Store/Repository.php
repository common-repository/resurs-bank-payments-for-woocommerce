<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Store\Store;
use Resursbank\Ecom\Lib\Model\Store\StoreCollection;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Get;
use Resursbank\Ecom\Lib\Repository\Cache;
use Throwable;

/**
 * Interaction with Store entities and related functionality.
 */
class Repository
{
    use ExceptionLog;

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     * @throws ConfigException
     */
    public static function getStores(
        int $size = 999999,
        ?int $page = null,
        array $sort = []
    ): StoreCollection {
        try {
            $cache = self::getCache(size: $size, page: $page, sort: $sort);
            $result = $cache->read();

            if (!$result instanceof StoreCollection) {
                $result = self::getApi(size: $size, page: $page, sort: $sort);

                $cache->write(data: $result);
            }
        } catch (Throwable $e) {
            self::logException(exception: $e);

            throw $e;
        }

        return $result;
    }

    /**
     * Retrieve list of stores stashed in cache.
     */
    public static function getCache(
        int $size = 999999,
        ?int $page = null,
        array $sort = []
    ): Cache {
        return new Cache(
            key: 'stores-' . sha1(
                string: serialize(value: compact('size', 'page', 'sort'))
            ),
            model: Store::class,
            ttl: 3600
        );
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws AttributeCombinationException
     */
    public static function getApi(
        int $size = 999999,
        ?int $page = null,
        array $sort = []
    ): StoreCollection {
        $result = (new Get(
            model: Store::class,
            route: Mapi::STORE_ROUTE,
            params: compact('size', 'page', 'sort'),
            extractProperty: 'content'
        ))->call();

        if (!$result instanceof StoreCollection) {
            throw new ApiException(message: 'Invalid API response.');
        }

        return $result;
    }
}
