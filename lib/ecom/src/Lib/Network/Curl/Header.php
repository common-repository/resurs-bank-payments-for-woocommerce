<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Curl;

use InvalidArgumentException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Model\Network\Header as HeaderModel;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Utilities\Generic;
use Throwable;

use function strlen;

/**
 * Handles construction and parsing of header data.
 */
class Header
{
    /**
     * @return array<array-key,HeaderModel>
     * @throws EmptyValueException
     * @throws ConfigException
     * @todo See constructor todo. If kept we should maybe change its visibility.
     */
    public static function generateHeaders(
        array $headers,
        string $payloadData,
        ContentType $contentType,
        bool $hasBodyData
    ): array {
        self::validateHeaderArray(headers: $headers);

        if (!self::hasHeader(headers: $headers, key: 'content-type')) {
            $headers[] = new HeaderModel(
                key: 'content-type',
                value: self::getContentType(contentType: $contentType)
            );
        }

        if (
            $hasBodyData &&
            !self::hasHeader(headers: $headers, key: 'content-length')
        ) {
            $headers[] = new HeaderModel(
                key: 'content-length',
                value: strlen(string: $payloadData)
            );
        }

        if (!self::hasHeader(headers: $headers, key: 'accept-language')) {
            $headers[] = new HeaderModel(key: 'accept-language', value: 'en');
        }

        return $headers;
    }

    /**
     * @throws ConfigException
     * @todo See constructor todo. If kept we should maybe change its visibility.
     */
    public static function hasHeader(
        array $headers,
        string $key
    ): bool {
        return count(self::findHeaders(headers: $headers, key: $key)) > 0;
    }

    /**
     * Retrieve list of headers where $key matches.
     *
     * @throws ConfigException
     * @todo See constructor todo. If kept we should maybe change its visibility.
     */
    public static function findHeaders(
        array $headers,
        string $key
    ): array {
        self::validateHeaderArray(headers: $headers);

        $key = strtolower(string: $key);

        return array_filter(
            array: $headers,
            callback: static fn ($header) => strtolower(
                string: $header->key
            ) === $key
        );
    }

    /**
     * @throws ConfigException
     */
    public static function getHeadersData(
        array $headers
    ): array {
        self::validateHeaderArray(headers: $headers);

        $result = [];

        /** @var HeaderModel $header | Confirmed by validateHeaderArray */
        foreach ($headers as $header) {
            $result[] = $header->key . ': ' . $header->value;
        }

        return $result;
    }

    /**
     * @throws ConfigException
     * @todo Add back what module class called Curl.
     * @todo Check if ConfigException validation needs a test.
     */
    public static function getUserAgent(): string
    {
        try {
            $version = (new Generic())->getVersionByComposer(location: __DIR__);
        } catch (Throwable) {
            $version = 'composer.version.not.found';
        }

        return implode(separator: ' +', array: array_filter(array: [
            Config::getUserAgent(),
            sprintf('ECom2-%s', $version),
            sprintf('PHP-%s', PHP_VERSION),
        ]));
    }

    /**
     * Get content type.
     */
    private static function getContentType(ContentType $contentType): string
    {
        return match ($contentType) {
            ContentType::EMPTY, ContentType::JSON => 'application/json; charset=utf-8',
            ContentType::URL => 'application/x-www-form-urlencoded; charset=utf-8',
            ContentType::RAW => 'text/plain; charset=utf-8'
        };
    }

    /**
     * @throws ConfigException
     */
    private static function validateHeaderArray(
        array $headers
    ): void {
        foreach ($headers as $header) {
            if (!$header instanceof HeaderModel) {
                $exception = new InvalidArgumentException(
                    message: 'Header must be an instance of Header.'
                );
                Config::getLogger()->error(message: $exception->getMessage());
                Config::getLogger()->error(message: $exception);
                throw $exception;
            }
        }
    }
}
