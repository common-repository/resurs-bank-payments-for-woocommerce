<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Callback\Http;

use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\HttpException;
use Resursbank\Ecom\Lib\Http\Controller;
use Resursbank\Ecom\Lib\Model\Callback\Authorization;

/**
 * Authorization callback controller.
 */
class AuthorizationController extends Controller
{
    /**
     * @throws HttpException
     * @throws ConfigException
     */
    public function getRequestData(): Authorization
    {
        $result = $this->getRequestModel(model: Authorization::class);

        if (!$result instanceof Authorization) {
            throw new HttpException(
                message: $this->translateError(phraseId: 'invalid-post-data'),
                code: 415
            );
        }

        return $result;
    }
}
