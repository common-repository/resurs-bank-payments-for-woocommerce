<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Enum;

/**
 * Enum for payment statuses when rejected.
 */
enum RejectedReasonCategory: string
{
    case UNKNOWN = 'UNKNOWN';
    case TECHNICAL_ERROR = 'TECHNICAL_ERROR';
    case CREDIT_DENIED = 'CREDIT_DENIED';
    case PAYMENT_FROZEN = 'PAYMENT_FROZEN';
    case TIMEOUT = 'TIMEOUT';
    case ABORTED_BY_CUSTOMER = 'ABORTED_BY_CUSTOMER';
    case INSUFFICIENT_FUNDS = 'INSUFFICIENT_FUNDS';
    case CANCELED = 'CANCELED';
}
