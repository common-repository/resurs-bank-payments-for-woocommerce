<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback\Enum;

/**
 * Possible order statuses from triggering test callback.
 */
enum TestStatus: string
{
    case OK = 'OK';
    case FAILED = 'FAILED';
    case INVALID_URL = 'INVALID_URL';
    case ERROR = 'ERROR';
    case TIMEOUT = 'TIMEOUT';
    case RATE_LIMITED = 'RATE_LIMITED';
}
