<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Get;
use Resursbank\Ecom\Lib\Repository\Cache;
use Resursbank\Ecom\Module\PaymentMethod\Api\ApplicationDataSpecification;
use Resursbank\Ecom\Module\PaymentMethod\Widget\UniqueSellingPoint;
use Throwable;

/**
 * Interaction with Payment Method entities and related functionality.
 */
class Repository
{
    use ExceptionLog;

    /**
     * NOTE: Parameters must be validated since they are utilized for our cache
     * keys.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    public static function getPaymentMethods(
        ?float $amount = null
    ): PaymentMethodCollection {
        try {
            $cache = self::getCache(amount: $amount);
            $result = $cache->read();

            if (!$result instanceof PaymentMethodCollection) {
                $result = self::getApi(amount: $amount)->call();

                if (!$result instanceof PaymentMethodCollection) {
                    throw new ApiException(message: 'Invalid API response.');
                }

                $result = self::setCollectionSortOrder(collection: $result);
                $cache->write(data: $result);
            }
        } catch (Throwable $e) {
            self::logException(exception: $e);

            throw $e;
        }

        return $result;
    }

    /**
     * Updates sort order of fetched payment methods.
     */
    public static function setCollectionSortOrder(
        PaymentMethodCollection $collection
    ): PaymentMethodCollection {
        /** @var PaymentMethod $method */
        foreach ($collection as $method) {
            /* @phpstan-ignore-next-line */
            $method->sortOrder = ((int) $collection->key() + 1) * 100;
        }

        return $collection;
    }

    /**
     * @throws ConfigException
     */
    public static function getCache(
        ?float $amount = null
    ): Cache {
        $storeId = Config::getStoreId();

        return new Cache(
            key: 'payment-methods-' . sha1(
                string: serialize(value: compact('storeId', 'amount'))
            ),
            model: PaymentMethod::class,
            ttl: 3600
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws ConfigException
     */
    public static function getApi(
        ?float $amount = null
    ): Get {
        return new Get(
            model: PaymentMethod::class,
            route: Mapi::STORE_ROUTE . '/' . Config::getStoreId() .
                '/payment_methods',
            params: compact('amount'),
            extractProperty: 'content'
        );
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws Throwable
     */
    public static function getById(
        string $paymentMethodId,
        ?float $amount = null
    ): ?PaymentMethod {
        $paymentMethods = self::getPaymentMethods(amount: $amount);

        try {
            return $paymentMethods->getById(methodId: $paymentMethodId);
        } catch (MissingValueException) {
            return null;
        }
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    public static function getApplicationDataSpecification(
        string $paymentMethodId,
        int $amount
    ): PaymentMethod\ApplicationFormSpecResponse {
        try {
            return (new ApplicationDataSpecification())->call(
                paymentMethodId: $paymentMethodId,
                amount: $amount
            );
        } catch (Throwable $e) {
            self::logException(exception: $e);
            throw $e;
        }
    }

    /**
     * Fetches the USP for specified payment method type
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public static function getUniqueSellingPoint(
        PaymentMethod $paymentMethod,
        float $amount
    ): UniqueSellingPoint {
        return new UniqueSellingPoint(
            paymentMethod: $paymentMethod,
            amount: $amount
        );
    }
}
