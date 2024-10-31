<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback\Enum;

/**
 * Callback types that Resurs Bank may send.
 */
enum CallbackType: string
{
    case AUTHORIZATION = 'AUTHORIZATION';
    case MANAGEMENT = 'MANAGEMENT';
}
