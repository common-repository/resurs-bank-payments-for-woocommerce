<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Http;

use JsonException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;
use Throwable;

use function file_get_contents;
use function get_class;

/**
 * Base controller class for JSON implementation. Execute arbitrary code,
 * construct a response as JSON encoded data.
 */
class Controller
{
    /**
     * Output JSON data.
     *
     * @throws ConfigException
     */
    public function respond(
        array $data
    ): string {
        try {
            $result = json_encode(value: $data, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $result = '{"error":"' . $this->translateError(
                phraseId: 'failed-to-encode'
            ) . '"}';
        }

        return $result;
    }

    /**
     * Shorthand method to log an Exception and create an error response.
     *
     * @throws ConfigException
     */
    public function respondWithError(Throwable $exception): string
    {
        $this->log(exception: $exception);
        return $this->respond(
            data: ['error' => $this->getErrorMessage(exception: $exception)]
        );
    }

    /**
     * Resolve error code from Exception
     */
    public function getErrorResponseCode(Throwable $exception): int
    {
        return match (get_class(object: $exception)) {
            HttpException::class => $exception->getCode(),
            CurlException::class => $exception->httpCode,
            default => 400
        };
    }

    /**
     * Mask messages from exceptions other than HttpException instances, to
     * ensure sensitive information is never rendered to the end client.
     *
     * @throws ConfigException
     */
    public function getErrorMessage(
        Throwable $exception
    ): string {
        return $exception instanceof HttpException ?
            $exception->getMessage() :
            $this->translateError(phraseId: 'unknown-error');
    }

    /**
     * Resolve decoded input data.
     *
     * @param class-string $model
     * @throws HttpException
     * @throws ConfigException
     */
    public function getRequestModel(
        string $model,
        ?stdClass $data = null
    ): Model {
        if ($data === null) {
            $data = $this->getInputDataAsStdClass();
        }

        try {
            return DataConverter::stdClassToType(object: $data, type: $model);
        } catch (Throwable $error) {
            // Attempt logging actual error.
            try {
                Config::getLogger()->error(message: $error);
            } catch (Throwable) {
                // Do nothing.
            }

            throw new HttpException(
                message: $this->translateError(phraseId: 'invalid-post-data'),
                code: 415
            );
        }
    }

    /**
     * Get raw input data as stdClass object.
     *
     * @throws HttpException
     * @throws ConfigException
     */
    public function getInputDataAsStdClass(): stdClass
    {
        try {
            $obj = json_decode(
                json: $this->getInputData(),
                associative: false,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            );

            if (!$obj instanceof stdClass) {
                throw new JsonException(message: 'Malformed data.');
            }
        } catch (JsonException) {
            throw new HttpException(
                message: $this->translateError(
                    phraseId: 'malformed-post-data'
                ),
                code: 406
            );
        }

        return $obj;
    }

    /**
     * @throws HttpException
     * @throws ConfigException
     */
    public function getInputData(): string
    {
        $data = file_get_contents(filename: 'php://input');

        if ($data === false || $data === '') {
            throw new HttpException(
                message: $this->translateError(phraseId: 'missing-post-data'),
                code: 400
            );
        }

        return $data;
    }

    public function log(
        Throwable $exception
    ): void {
        try {
            Config::getLogger()->debug(message: $exception);
        } catch (Throwable) {
            // Logging is optional. Silence.
        }
    }

    /**
     * Translate error message without tossing Exception.
     *
     * @throws ConfigException
     */
    public function translateError(
        string $phraseId
    ): string {
        try {
            $result = Translator::translate(phraseId: $phraseId);
        } catch (Throwable $error) {
            $result = 'Failed to translate error. Check debug log for info.';
            Config::getLogger()->error(message: $error);
        }

        return $result;
    }
}
