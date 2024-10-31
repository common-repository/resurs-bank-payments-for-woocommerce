<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback\Enum;

/**
 * Possible order statuses during callback.
 *
 * @see https://merchant-api.integration.resurs.com/docs/v2/merchant_payments_v2/options#callbacks
 */
enum Status: string
{
    case AUTHORIZED = 'AUTHORIZED';
    case CAPTURED = 'CAPTURED';
    case FROZEN = 'FROZEN';
    case REJECTED = 'REJECTED';
}
