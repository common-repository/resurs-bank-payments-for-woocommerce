<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Repository\Traits;

use JsonException;
use ReflectionException;
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
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Network\AuthType;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\Curl;
use Resursbank\Ecom\Lib\Network\RequestMethod;

/**
 * HTTP Requests centralized for MAPI related calls.
 */
class Request
{
    use ExceptionLog;
    use ModelConverter;
    use DataResolver;

    /**
     * @param class-string $model | Convert cached data to model instance(s).
     * @throws IllegalTypeException
     */
    public function __construct(
        protected readonly string $model,
        protected readonly string $route,
        protected readonly RequestMethod $requestMethod,
        protected Mapi $api,
        protected readonly array $params = [],
        protected readonly string $extractProperty = '',
        protected readonly array $headers = [],
        protected readonly ContentType $contentType = ContentType::JSON
    ) {
        $this->validateModel(model: $model);
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
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function call(bool $forceObject = false): Collection|Model
    {
        $curl = new Curl(
            url: $this->api->getUrl(
                route: $this->route
            ),
            requestMethod: $this->requestMethod,
            headers: $this->headers,
            payload: $this->params,
            contentType: $this->contentType,
            authType: AuthType::JWT,
            responseContentType: ContentType::JSON,
            forceObject: $forceObject
        );

        return $this->convertToModel(
            data: $this->resolveResponseData(
                data: $curl->exec()->body,
                extractProperty: $this->extractProperty
            ),
            model: $this->model
        );
    }
}
