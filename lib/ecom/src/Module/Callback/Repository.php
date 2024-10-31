<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Callback;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Callback\Authorization;
use Resursbank\Ecom\Lib\Model\Callback\CallbackInterface;
use Resursbank\Ecom\Lib\Model\Callback\Management;
use Resursbank\Ecom\Lib\Model\Callback\TestResponse;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\Post;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\PaymentHistory\Repository as PaymentHistoryRepository;
use Throwable;

/**
 * Callback repository.
 */
class Repository
{
    use ExceptionLog;

    /**
     * Trigger test callback.
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
     * @noinspection PhpUnused
     */
    public static function triggerTest(
        string $url,
        StringValidation $stringValidation = new StringValidation()
    ): TestResponse {
        Config::getLogger()->debug(message: 'Triggering test callback.');

        $stringValidation->isUrl(value: $url);

        $request = new Post(
            model: TestResponse::class,
            route: Mapi::CALLBACK_ROUTE . '/test',
            params: ['url' => $url]
        );

        $response = $request->call();

        if (!$response instanceof TestResponse) {
            throw new IllegalValueException(
                message: 'Unexpected model instance returned from test callback.'
            );
        }

        return $response;
    }

    /**
     * @throws ConfigException
     */
    public static function process(
        CallbackInterface $callback,
        callable $process
    ): int {
        $paymentId = $callback->getCheckoutId() ?? $callback->getPaymentId();

        self::trackInit(paymentId: $paymentId, callback: $callback);
        self::addDebugLogs(callback: $callback);

        $code = 202;

        try {
            $process($callback);

            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $paymentId,
                event: Event::CALLBACK_COMPLETED,
                user: User::RESURSBANK,
                result: Result::SUCCESS
            ));
        } catch (Throwable $e) {
            self::logException(exception: $e);
            $code = 408;

            if ($e instanceof HttpException) {
                $code = $e->getCode();
            }

            self::trackError(paymentId: $paymentId, error: $e);
        }

        Config::getLogger()->debug(message: "Responding with code $code");

        return $code;
    }

    /**
     * Log error in payment history.
     *
     * @throws ConfigException
     */
    public static function trackError(
        string $paymentId,
        Throwable $error
    ): void {
        try {
            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $paymentId,
                event: Event::CALLBACK_FAILED,
                user: User::ADMIN,
                extra: PaymentHistoryRepository::getError(error: $error),
                result: Result::ERROR
            ));
        } catch (Throwable $e) {
            self::logException(exception: $e);
        }
    }

    /**
     * Log callback initialization in payment history.
     *
     * @throws ConfigException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public static function trackInit(
        string $paymentId,
        CallbackInterface $callback
    ): void {
        try {
            $extra = null;

            if ($callback instanceof Authorization) {
                $event = Event::CALLBACK_AUTHORIZATION;
                $extra = $callback->status->value;
            } else {
                $event = Event::CALLBACK_MANAGEMENT;
            }

            PaymentHistoryRepository::write(entry: new Entry(
                paymentId: $paymentId,
                event: $event,
                user: User::RESURSBANK,
                extra: $extra
            ));
        } catch (Throwable $e) {
            self::logException(exception: $e);
        }
    }

    /**
     * Append debug log entries.
     *
     * @throws ConfigException
     */
    public static function addDebugLogs(
        CallbackInterface $callback
    ): void {
        if ($callback instanceof Management) {
            Config::getLogger()->debug(
                message: sprintf(
                    'Processing management callback for %s, action %s (%s)',
                    $callback->getPaymentId(),
                    $callback->action->value,
                    $callback->actionId
                )
            );
        }

        if (!($callback instanceof Authorization)) {
            return;
        }

        Config::getLogger()->debug(
            message: sprintf(
                'Processing authorization callback for %s, status %s',
                $callback->getPaymentId(),
                $callback->status->value
            )
        );
    }
}
