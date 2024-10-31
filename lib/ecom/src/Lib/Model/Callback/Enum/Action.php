<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback\Enum;

/**
 * Possible order actions during callback.
 */
enum Action: string
{
    case CAPTURE = 'CAPTURE';
    case REFUND = 'REFUND';
    case CANCEL = 'CANCEL';
    case MODIFY_ORDER = 'MODIFY_ORDER';
}
