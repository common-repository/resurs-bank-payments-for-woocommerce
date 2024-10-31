<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store\Http;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Model\Store\GetStoresRequest;

/**
 * Basic controller functionality to collect stores based on credentials.
 */
class GetStoresController extends Controller
{
    /**
     * @throws HttpException
     * @throws ConfigException
     */
    public function getRequestData(): GetStoresRequest
    {
        $result = $this->getRequestModel(model: GetStoresRequest::class);

        if (!$result instanceof GetStoresRequest) {
            throw new HttpException(
                message: $this->translateError(phraseId: 'invalid-post-data'),
                code: 415
            );
        }

        return $result;
    }
}
