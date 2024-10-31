<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Repository\Api\Mapi;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Api\Mapi;
use Resursbank\Ecom\Lib\Log\Traits\ExceptionLog;
use Resursbank\Ecom\Lib\Network\ContentType;
use Resursbank\Ecom\Lib\Network\RequestMethod;
use Resursbank\Ecom\Lib\Repository\Traits\DataResolver;
use Resursbank\Ecom\Lib\Repository\Traits\ModelConverter;
use Resursbank\Ecom\Lib\Repository\Traits\Request;

/**
 * Generic functionality to perform a GET call against the Merchant API and
 * convert the response to model instance(s).
 */
class Get extends Request
{
    use ExceptionLog;
    use ModelConverter;
    use DataResolver;

    /**
     * @param class-string $model | Convert cached data to model instance(s).
     * @throws IllegalTypeException
     */
    public function __construct(
        string $model,
        string $route,
        array $params = [],
        string $extractProperty = ''
    ) {
        parent::__construct(
            model: $model,
            route: $route,
            requestMethod: RequestMethod::GET,
            api: new Mapi(),
            params: $params,
            extractProperty: $extractProperty,
            contentType: ContentType::URL
        );
    }
}
