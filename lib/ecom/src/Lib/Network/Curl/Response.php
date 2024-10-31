<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Curl;

use CurlHandle;
use JsonException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use stdClass;

use function is_int;

/**
 * Collection of methods to handle CURL request response data.
 */
class Response
{
    /**
     * Resolve body content from request response as object, decoded from JSON.
     *
     * @throws IllegalValueException
     * @throws NotJsonEncodedException
     * @throws EmptyValueException
     * @throws JsonException
     */
    public static function getJsonBody(
        string $body,
        StringValidation $stringValidation = new StringValidation()
    ): stdClass {
        $stringValidation->notEmpty(value: $body);
        $stringValidation->isJson(value: $body);

        $content = json_decode(
            json: $body,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        if (!$content instanceof stdClass) {
            throw new IllegalValueException(
                message: 'Decoded JSON body is not an object.'
            );
        }

        return $content;
    }

    /**
     * Type-safe wrapper to extract response code from request.
     *
     * @throws IllegalTypeException
     */
    public static function getCode(CurlHandle $ch): int
    {
        $code = curl_getinfo(handle: $ch, option: CURLINFO_RESPONSE_CODE);

        if (is_numeric(value: $code)) {
            $code = (int) $code;
        }

        if (!is_int(value: $code)) {
            throw new IllegalTypeException(
                message: 'Curl response code is not an integer.'
            );
        }

        return $code;
    }
}
