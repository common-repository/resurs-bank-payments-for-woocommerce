<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Api;

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
use Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;

/**
 * Application data specification.
 */
class ApplicationDataSpecification
{
    private Mapi $mapi;

    public function __construct()
    {
        $this->mapi = new Mapi();
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
     */
    public function call(
        string $paymentMethodId,
        int $amount
    ): ApplicationFormSpecResponse {
        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::STORE_ROUTE . '/' . Config::getStoreId() .
                '/payment_methods/' . $paymentMethodId .
                '/application_data_specification'
            ),
            requestMethod: RequestMethod::GET,
            payload: ['amount' => $amount],
            contentType: ContentType::URL,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON
        );

        $data = $curl->exec()->body;

        if (!$data instanceof stdClass) {
            throw new ApiException(
                message: 'Invalid response from API. Not an stdClass.',
                code: 500
            );
        }

        $result = DataConverter::stdClassToType(
            object: $data,
            type: ApplicationFormSpecResponse::class
        );

        if (!$result instanceof ApplicationFormSpecResponse) {
            throw new IllegalValueException(
                message: 'Response is not an instance of ' . ApplicationFormSpecResponse::class
            );
        }

        return $result;
    }
}
