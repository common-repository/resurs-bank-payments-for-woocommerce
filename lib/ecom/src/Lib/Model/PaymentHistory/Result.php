<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentHistory;

/**
 * Available event types. Helps us filter/mark log entries.
 */
enum Result: string
{
    case INFO = 'INFO';
    case ERROR = 'ERROR';
    case SUCCESS = 'SUCCESS';
}
