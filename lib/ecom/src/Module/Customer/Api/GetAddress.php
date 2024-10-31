<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Customer\Api;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\GetAddressException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Throwable;

/**
 * GET /payments/{orderReference}, similar to soap/RCO-REST getPayment,but for MAPI.
 */
class GetAddress
{
    /**
     * Assign object properties.
     */
    public function __construct(
        private readonly Mapi $mapi = new Mapi()
    ) {
    }

    /**
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws GetAddressException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ApiException
     * @SuppressWarnings(PHPMD.Superglobals)
     * @todo Refactor, see ECP-356. Remove phpcs:ignore when done.
     */
    // phpcs:ignore
    public function call(
        string $governmentId,
        CustomerType $customerType
    ): Address {
        // REMOTE_ADDR is normally present, however - if this is running from console or similar (when REMOTE_ADDR
        // is simply absent) we should add localhost as remote.
        $payload = [
            'storeId' => Config::getStoreId(),
            'customerIp' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'governmentId' => $governmentId,
            'customerType' => $customerType->value,
        ];

        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::CUSTOMER_ROUTE . '/address/by_government_id'
            ),
            requestMethod: RequestMethod::POST,
            payload: $payload,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        try {
            $data = $curl->exec()->body;
        } catch (Throwable $e) {
            throw new GetAddressException(
                message: sprintf(
                    'Customer address request error: %s (%d).',
                    $e->getMessage(),
                    $e->getCode()
                ),
                previous: $e
            );
        }

        $content = (
            $data instanceof stdClass &&
            $data->address instanceof stdClass
        ) ? $data->address : new stdClass();

        $result = DataConverter::stdClassToType(
            object: $content,
            type: Address::class
        );

        if (!$result instanceof Address) {
            throw new InvalidTypeException(
                message: 'Expected PaymentCollection.'
            );
        }

        return $result;
    }
}
