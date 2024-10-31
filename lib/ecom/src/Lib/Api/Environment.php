<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

/**
 * API environment options.
 *
 * @codingStandardsIgnoreStart
 */
enum Environment: string
{
    case TEST = 'test';
    case PROD = 'prod';
}
