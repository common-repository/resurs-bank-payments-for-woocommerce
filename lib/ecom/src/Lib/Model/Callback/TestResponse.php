<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use Resursbank\Ecom\Lib\Model\Callback\Enum\TestStatus;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of response from triggering test callback.
 */
class TestResponse extends Model
{
    /**
     * @param int $code HTTP code.
     */
    public function __construct(
        public readonly TestStatus $status,
        public readonly int $code
    ) {
    }
}
