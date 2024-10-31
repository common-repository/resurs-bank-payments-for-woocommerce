<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Api;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Model\PaymentHistory\User;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Module\PaymentHistory\Repository as PaymentHistoryRepository;
use Resursbank\Ecom\Module\PaymentHistory\Translator;
use stdClass;

/**
 * POST /payments/{payment_id}/capture
 */
class Capture
{
    private Mapi $mapi;

    public function __construct()
    {
        $this->mapi = new Mapi();
    }

    /**
     * Makes call to the API
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws FilesystemException
     * @throws TranslationException
     */
    public function call(
        string $paymentId,
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null,
        ?string $transactionId = null,
        ?string $invoiceId = null
    ): Payment {
        PaymentHistoryRepository::write(
            entry: new Entry(
                paymentId: $paymentId,
                event: Event::CAPTURE_REQUESTED,
                user: User::ADMIN
            )
        );

        $payload = $this->getPayload(
            orderLines: $orderLines,
            creator: $creator,
            transactionId: $transactionId,
            invoiceId: $invoiceId
        );

        $curl = $this->getCurlObject(paymentId: $paymentId, payload: $payload);
        $data = $curl->exec()->body;

        $content = $data instanceof stdClass ? $data : new stdClass();

        $result = DataConverter::stdClassToType(
            object: $content,
            type: Payment::class
        );

        if (!$result instanceof Payment) {
            $this->logFailure(paymentId: $paymentId);
            throw new IllegalTypeException(message: 'Expected Payment');
        }

        $this->logSuccess(
            paymentId: $paymentId,
            result: $result,
            orderLines: $orderLines
        );
        return $result;
    }

    /**
     * Logs failure to payment history.
     *
     * @throws TranslationException
     * @throws IllegalValueException
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ConfigException
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws FilesystemException
     */
    private function logFailure(string $paymentId): void
    {
        PaymentHistoryRepository::write(entry: new Entry(
            paymentId: $paymentId,
            event: Event::REQUEST_FAILED,
            user: User::ADMIN,
            result: Result::ERROR,
            extra: Translator::translate(phraseId: 'event-request-failed')
        ));
    }

    /**
     * Logs success to payment history.
     *
     * @throws ConfigException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws JsonException
     */
    private function logSuccess(
        string $paymentId,
        Payment $result,
        ?OrderLineCollection $orderLines = null
    ): void {
        PaymentHistoryRepository::write(
            entry: new Entry(
                paymentId: $paymentId,
                event: $result->isCaptured() ? Event::CAPTURED
                    : Event::PARTIALLY_CAPTURED,
                user: User::ADMIN,
                result: Result::SUCCESS,
                extra: empty($orderLines)
                    ? null : Price::format(value: $orderLines->getTotal())
            )
        );
    }

    /**
     * Get Curl object.
     *
     * @throws ApiException
     * @throws AttributeCombinationException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private function getCurlObject(string $paymentId, array $payload): Curl
    {
        return new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::PAYMENT_ROUTE . '/' . $paymentId . '/capture'
            ),
            requestMethod: RequestMethod::POST,
            payload: $payload,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON,
            forceObject: empty($payload)
        );
    }

    /**
     *  Prepare payload.
     */
    private function getPayload(
        ?OrderLineCollection $orderLines = null,
        ?string $creator = null,
        ?string $transactionId = null,
        ?string $invoiceId = null
    ): array {
        $payload = [];

        if ($orderLines) {
            $payload['orderLines'] = $orderLines->toArray();
        }

        if ($creator) {
            $payload['creator'] = $creator;
        }

        if ($transactionId) {
            $payload['transactionId'] = $transactionId;
        }

        if ($invoiceId) {
            $payload['invoiceOptions'] = ['invoiceId' => $invoiceId];
        }

        return $payload;
    }
}
