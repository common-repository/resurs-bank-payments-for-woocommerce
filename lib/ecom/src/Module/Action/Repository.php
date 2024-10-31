<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Action;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Get;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Repository to interact with Action object at Resurs Bank.
 */
class Repository
{
    use ExceptionLog;

    /**
     * Fetch Action from API.
     *
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws AttributeCombinationException
     */
    public static function getAction(
        string $paymentId,
        string $actionId
    ): ActionLog {
        self::validatePaymentId(paymentId: $paymentId);
        self::validateActionId(actionId: $actionId);

        $result = (new Get(
            model: ActionLog::class,
            route: Mapi::PAYMENT_ROUTE . "/$paymentId/actions/$actionId",
            params: []
        ))->call();

        if (!$result instanceof ActionLog) {
            throw new ApiException(message: 'Invalid API response.');
        }

        return $result;
    }

    /**
     * @throws IllegalValueException
     */
    private static function validatePaymentId(
        string $paymentId
    ): void {
        (new StringValidation())->isUuid(value: $paymentId);
    }

    /**
     * @throws IllegalValueException
     */
    private static function validateActionId(
        string $actionId
    ): void {
        (new StringValidation())->isUuid(value: $actionId);
    }
}
