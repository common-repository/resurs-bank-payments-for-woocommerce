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
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Application;
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Options;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;

/**
 * POST /payments/{payment_id}/create
 *
 * @todo Refactor ECP-358. Remove phpcs:ignore below when done.
 */
// phpcs:ignore
class Create
{
    private Mapi $mapi;

    /**
     * Assign properties.
     */
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @todo When refactored, remove phpcs:ignore below and other suppressors above.
     */
    // phpcs:ignore
    public function call(
        string $paymentMethodId,
        OrderLineCollection $orderLines,
        ?string $orderReference = null,
        ?Application $application = null,
        ?Customer $customer = null,
        ?Metadata $metadata = null,
        ?Options $options = null
    ): Payment {
        $params = [
            'storeId' => Config::getStoreId(),
            'paymentMethodId' => $paymentMethodId,
            'order' => [
                'orderLines' => $orderLines->toArray(),
            ],
        ];

        if ($orderReference) {
            $params['order']['orderReference'] = $orderReference;
        }

        if ($application) {
            $params['application'] = $application;
        }

        if ($customer) {
            // If governmentId is empty or null, remove it from the payload.
            // Some payment methods require this field to be removed, if empty.
            if (empty($customer->governmentId)) {
                unset($customer->governmentId);
            }

            $params['customer'] = $customer;
        }

        if ($metadata) {
            //$params['metadata'] = $metadata;
            // @todo Find a prettier solution to the issue of Metadata::custom being turned into an empty object
            //   when passed through json_encode.
            $params['metadata'] = new stdClass();

            if (isset($metadata->custom)) {
                $params['metadata']->custom = $metadata->custom->toArray();
            }
        }

        if ($options) {
            $params['options'] = $options;
        }

        $curl = new Curl(
            url: $this->mapi->getUrl(
                route: Mapi::PAYMENT_ROUTE
            ),
            requestMethod: RequestMethod::POST,
            payload: $params,
            contentType: ContentType::JSON,
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
            type: Payment::class
        );

        if (!$result instanceof Payment) {
            throw new IllegalValueException(
                message: 'Response is not an instance of ' . Payment::class
            );
        }

        return $result;
    }
}
