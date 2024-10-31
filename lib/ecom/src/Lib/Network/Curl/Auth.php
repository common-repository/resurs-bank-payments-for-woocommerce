<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Curl;

use CurlHandle;
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
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt\Token;
use Resursbank\Ecom\Lib\Repository\Api\Mapi\GenerateToken as GenerateMapiToken;

/**
 * JWT-related functionality for Curl.
 */
class Auth
{
    /**
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ValidationException
     * @throws ReflectionException
     * @throws ApiException
     * @throws ConfigException
     * @throws AttributeCombinationException
     */
    public static function setJwtAuth(CurlHandle $ch): void
    {
        $auth = Config::getJwtAuth();

        if ($auth === null) {
            $exception = new ConfigException(
                message: 'JWT auth is not configured.'
            );
            Config::getLogger()->error(message: $exception->getMessage());
            Config::getLogger()->error(message: $exception);
            throw $exception;
        }

        curl_setopt(
            handle: $ch,
            option: CURLOPT_HTTPAUTH,
            value: CURLAUTH_BEARER
        );

        curl_setopt(
            handle: $ch,
            option: CURLOPT_XOAUTH2_BEARER,
            value: self::getJwtToken(auth: $auth)->access_token
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
     * @throws IllegalValueException
     * @throws NotJsonEncodedException
     */
    private static function getJwtToken(
        Jwt $auth
    ): Token {
        $result = $auth->getToken();

        if ($result === null || $result->isExpired()) {
            return (new GenerateMapiToken(auth: $auth))->call();
        }

        return $result;
    }
}
