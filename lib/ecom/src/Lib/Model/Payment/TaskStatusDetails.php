<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Status\CustomerTaskData;
use Resursbank\Ecom\Lib\Model\Payment\Status\MerchantTaskData;

/**
 * Details about tasks relating to a payment.
 *
 * NOTE: This is not actually part of the Payment object, but it relates
 * directly to payments and is collected from a sub-endpoint of the payment API.
 */
class TaskStatusDetails extends Model
{
    /**
     * Construct object.
     *
     * @todo Validation ECP-414
     */
    public function __construct(
        public bool $completed,
        public ?CustomerTaskData $customer = null,
        public ?MerchantTaskData $merchant = null,
        public ?CustomerTaskData $coApplication = null
    ) {
    }
}
