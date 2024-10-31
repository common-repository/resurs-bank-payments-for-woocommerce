<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Curl;

use CurlHandle;
use JsonException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\NotJsonEncodedException;
use Resursbank\Ecom\Lib\Network\ContentType;
use stdClass;

use function is_string;

/**
 * Error handling of curl requests.
 */
class ErrorHandler
{
    public readonly int $httpCode;

    /**
     * @throws IllegalTypeException
     */
    public function __construct(
        public readonly string|bool $body,
        public readonly CurlHandle $ch,
        public readonly ContentType $contentType
    ) {
        $this->httpCode = Response::getCode(ch: $ch);
    }

    /**
     * @throws AuthException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     */
    public function validate(): void
    {
        if ($this->body === false) {
            $this->throwCurlException();
        }

        $this->validateBody();
        $this->validateHttpCode();
    }

    /**
     * @throws CurlException
     */
    private function throwCurlException(?string $message = null): void
    {
        throw new CurlException(
            message: $message ?? curl_error(handle: $this->ch),
            code: curl_errno(handle: $this->ch),
            body: $this->body,
            httpCode: $this->httpCode
        );
    }

    /**
     * Resolve error information from response body (requests may succeed while
     * containing an error, for example a request resulting in a 400 may contain
     * information about what went wrong while the request itself went fine).
     */
    private function getErrorMessageFromBody(stdClass $content): string
    {
        $data = [];

        if (is_numeric(value: $content->code ?? null)) {
            $data[] = (string) $content->code;
        }

        if (is_string(value: $content->message ?? null)) {
            $data[] = $content->message;
        }

        if (is_string(value: $content->traceId ?? null)) {
            $data[] = '[Trace ID: ' . $content->traceId . ']';
        }

        return implode(separator: ', ', array: $data);
    }

    /**
     * @throws AuthException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws NotJsonEncodedException
     * @throws CurlException
     * @throws EmptyValueException
     */
    private function validateBody(): void
    {
        if (!is_string(value: $this->body)) {
            throw new IllegalTypeException(message: 'Body is not a string.');
        }

        if ($this->contentType !== ContentType::JSON) {
            return;
        }

        $content = Response::getJsonBody(body: (string) $this->body);

        if (isset($content->error) && $content->error === 'invalid_client') {
            throw new AuthException(
                message: 'Access denied. Please verify user credentials.'
            );
        }

        if ($this->httpCode < 400) {
            return;
        }

        $this->throwCurlException(
            message: $this->getErrorMessageFromBody(content: $content)
        );
    }

    /**
     * @throws AuthException
     * @throws CurlException
     */
    private function validateHttpCode(): void
    {
        if ($this->httpCode < 400 && $this->httpCode >= 100) {
            return;
        }

        if ($this->httpCode === 401) {
            throw new AuthException(
                message: 'Access denied. Please verify user credentials.'
            );
        }

        $this->throwCurlException();
    }
}
