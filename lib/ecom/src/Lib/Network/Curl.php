<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network;

use CurlHandle;
use Exception;
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
use Resursbank\Ecom\Lib\Model\Network\Response;
use Resursbank\Ecom\Lib\Network\Curl\Auth;
use Resursbank\Ecom\Lib\Network\Curl\ErrorHandler;
use Resursbank\Ecom\Lib\Network\Curl\Header;
use Resursbank\Ecom\Lib\Network\Curl\Response as ResponseHandler;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use stdClass;

/**
 * Curl connection wrapper.
 *
 * @noinspection EfferentObjectCouplingInspection
 */
class Curl
{
    public readonly CurlHandle $ch;

    public readonly ContentType $responseContentType;

    /**
     * @param bool $forceObject Enforces the JSON_FORCE_OBJECT flag on json_encode of payload
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        string $url,
        public readonly RequestMethod $requestMethod,
        array $headers = [],
        array $payload = [],
        public readonly ContentType $contentType = ContentType::JSON,
        public readonly AuthType $authType = AuthType::JWT,
        public readonly ApiType $apiType = ApiType::MERCHANT,
        ?ContentType $responseContentType = null,
        private readonly bool $forceObject = false,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->responseContentType = $responseContentType ?? $contentType;

        // Initialize Curl.
        $ch = $this->init(url: $url, headers: $headers, payload: $payload);

        // Setup plaintext auth if requested.
        $this->setAuth(ch: $ch);

        $this->ch = $ch;
    }

    /**
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
     */
    public static function get(
        string $url,
        array $payload = [],
        AuthType $authType = AuthType::JWT
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::GET,
            payload: $payload,
            contentType: ContentType::URL,
            authType: $authType
        );

        return $curl->exec();
    }

    /**
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
     */
    public static function post(
        string $url,
        array $payload = [],
        AuthType $authType = AuthType::JWT
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::POST,
            payload: $payload,
            authType: $authType
        );

        return $curl->exec();
    }

    /**
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
     */
    public static function delete(
        string $url,
        AuthType $authType = AuthType::JWT
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::DELETE,
            authType: $authType
        );

        return $curl->exec();
    }

    /**
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
     */
    public static function put(
        string $url,
        array $payload = [],
        AuthType $authType = AuthType::JWT,
        ContentType $contentType = ContentType::JSON,
        ?ContentType $responseContentType = null
    ): Response {
        $curl = new self(
            url: $url,
            requestMethod: RequestMethod::PUT,
            payload: $payload,
            contentType: $contentType,
            authType: $authType,
            responseContentType: $responseContentType
        );

        return $curl->exec();
    }

    /**
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @throws NotJsonEncodedException
     */
    public function exec(): Response
    {
        $body = curl_exec(handle: $this->ch);
        $code = ResponseHandler::getCode(ch: $this->ch);

        // Validate request response.
        $errorHandler = new ErrorHandler(
            body: $body,
            ch: $this->ch,
            contentType: $this->responseContentType
        );

        try {
            $errorHandler->validate();

            /* Having passed validation means $body must be a string, since we
               always apply CURLOPT_RETURNTRANSFER. */
            $body = (string) $body;
        } catch (CurlException | IllegalTypeException | AuthException $error) {
            Config::getLogger()->error(message: $error);
            throw $error;
        }

        if ($this->responseContentType === ContentType::JSON) {
            $body = ResponseHandler::getJsonBody(body: $body);
        } elseif ($this->responseContentType === ContentType::RAW) {
            $body = (object) ['message' => $body];
        }

        if (!$body instanceof stdClass) {
            throw new IllegalTypeException(message: 'Body is not an object.');
        }

        curl_close(handle: $this->ch);

        return new Response(body: $body, code: $code);
    }

    /**
     * @throws JsonException
     * @throws ValidationException
     */
    public function generateUrl(string $url, array $payload): string
    {
        $url .= $this->requestMethod !== RequestMethod::GET || empty($payload)
            ? '' :
            '?' . $this->getPayloadData(payload: $payload);

        $this->stringValidation->isUrl(value: $url);

        return $url;
    }

    /**
     * @throws JsonException
     * @todo Consider caching this is a local variable on this instance to avoid subsequent calls. NOTE: Generating this
     * @todo data directly in the constructor harms refactoring.
     */
    public function getPayloadData(
        array $payload
    ): string {
        $flags = JSON_THROW_ON_ERROR;

        if ($this->forceObject) {
            $flags = JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT;
        }

        return match ($this->contentType) {
            ContentType::EMPTY, ContentType::RAW => '',
            ContentType::JSON => json_encode(
                value: $payload,
                flags: JSON_THROW_ON_ERROR | $flags
            ),
            ContentType::URL => http_build_query(data: $payload)
        };
    }

    /**
     * @throws JsonException
     * @throws ValidationException
     * @throws Exception
     * @todo Check if CURLOPT_ENCODING should be included and what value it should be assigned.
     */
    private function init(
        string $url,
        array $headers,
        array $payload
    ): CurlHandle {
        /** @noinspection DuplicatedCode */
        $ch = curl_init();

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            // Don't treat HTTP code 400+ as error.
            CURLOPT_FAILONERROR => false,
            // Follow redirects.
            CURLOPT_AUTOREFERER => true,
            // Track outgoing headers for debugging.
            CURLINFO_HEADER_OUT => true,
            // Do not include header in output.
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => Header::getUserAgent(),
            CURLOPT_HTTPHEADER => Header::getHeadersData(
                headers: Header::generateHeaders(
                    headers: $headers,
                    payloadData: $this->getPayloadData(payload: $payload),
                    contentType: $this->contentType,
                    hasBodyData: $this->requestMethod !== RequestMethod::GET
                )
            ),
            CURLOPT_CUSTOMREQUEST => $this->requestMethod->value,
            CURLOPT_URL => $this->generateUrl(url: $url, payload: $payload),
            CURLOPT_SSLVERSION => CURL_SSLVERSION_DEFAULT,
        ];

        if (!empty(Config::getProxy())) {
            $options[CURLOPT_PROXY] = Config::getProxy();
            $options[CURLOPT_PROXYTYPE] = Config::getProxyType();
        }

        if (Config::getTimeout()) {
            $options[CURLOPT_CONNECTTIMEOUT] = ceil(
                num: Config::getTimeout()
            ) / 2;
            $options[CURLOPT_TIMEOUT] = ceil(num: Config::getTimeout());
        }

        curl_setopt_array(handle: $ch, options: $options);

        $this->setContent(ch: $ch, payload: $payload);

        return $ch;
    }

    /**
     * Append POST | PUT data / options to CURL.
     *
     * @throws JsonException
     */
    private function setContent(CurlHandle $ch, array $payload): void
    {
        if ($this->contentType === ContentType::EMPTY) {
            return;
        }

        $data = $this->getPayloadData(payload: $payload);

        if ($data !== '' && $this->requestMethod !== RequestMethod::GET) {
            curl_setopt(handle: $ch, option: CURLOPT_POSTFIELDS, value: $data);
        }

        if ($this->requestMethod !== RequestMethod::POST) {
            return;
        }

        curl_setopt(handle: $ch, option: CURLOPT_POST, value: true);
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws ConfigException
     * @throws AttributeCombinationException
     */
    private function setAuth(CurlHandle $ch): void
    {
        switch ($this->authType) {
            case AuthType::JWT:
                Auth::setJwtAuth(ch: $ch);
                break;

            case AuthType::NONE:
                break;
        }
    }
}
