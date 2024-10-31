<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentHistory;

/**
 * What type of user/system invoked an event, and their corresponding
 * translation strings.
 */
enum User: string
{
    case CUSTOMER = 'user-customer';
    case RESURSBANK = 'user-resursbank';
    case CRON = 'user-cron';
    case ADMIN = 'user-admin';
}
