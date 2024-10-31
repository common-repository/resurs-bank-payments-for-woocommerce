<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Status;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Merchant task data.
 */
class MerchantTaskData extends Model
{
    /**
     * Construct object.
     *
     * @todo Validation ECP-412
     */
    public function __construct(
        public string $merchantUrl
    ) {
    }
}
