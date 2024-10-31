<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Api;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Model\PaymentCollection;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use Resursbank\Ecom\Lib\Utilities\Strings;
use stdClass;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

use function is_array;

/**
 * POST /payments/find_payment for looking up payments in MAPI. Can be used to find legacy payments.
 */
class Search
{
    /**
     * Assign properties.
     */
    public function __construct(
        private readonly Mapi $mapi = new Mapi()
    ) {
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
     * @throws ValidationException
     * @throws AttributeCombinationException
     * @throws NotJsonEncodedException
     * @todo Refactor ECP-357. Remove phpcs:ignore when done.
     */
    // phpcs:ignore
    public function call(
        ?string $orderReference = null,
        ?string $governmentId = null,
        ?string $storeId = null
    ): PaymentCollection {
        $payload = $this->getPayload(
            orderReference: $orderReference,
            governmentId: $governmentId,
            storeId: $storeId
        );

        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::PAYMENT_ROUTE . '/search'
            ),
            requestMethod: RequestMethod::POST,
            payload: $payload,
            contentType: ContentType::JSON,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $data = $curl->exec()->body;

        $content = (
            $data instanceof stdClass &&
            isset($data->content) &&
            is_array(value: $data->content)
        ) ? $data->content : [];

        $result = DataConverter::arrayToCollection(
            data: $content,
            type: Payment::class
        );

        if (!$result instanceof PaymentCollection) {
            throw new InvalidTypeException(
                message: 'Expected PaymentCollection.'
            );
        }

        return $result;
    }

    /**
     * Prepare and return the payload for the search.
     *
     * @throws ConfigException
     */
    private function getPayload(
        ?string $orderReference = null,
        ?string $governmentId = null,
        ?string $storeId = null
    ): array {
        $payload = [];

        if ($governmentId && trim(string: $governmentId) !== '') {
            $payload['governmentId'] = $governmentId;
        }

        if ($orderReference && trim(string: $orderReference) !== '') {
            $payload['orderReference'] = $orderReference;
        }

        if (!Strings::isUuid(value: $storeId ?? '')) {
            $storeId = Config::getStoreId();
        }

        $payload['storeId'] = $storeId;

        return $payload;
    }
}
