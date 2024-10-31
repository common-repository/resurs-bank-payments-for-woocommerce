<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Status;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Customer task data.
 */
class CustomerTaskData extends Model
{
    /**
     * Construct object.
     *
     * @todo Validation ECP-413
     */
    public function __construct(
        public string $customerUrl,
        public bool $hasActiveTask
    ) {
    }
}
