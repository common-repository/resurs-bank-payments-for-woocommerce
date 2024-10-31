<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

/**
 * URLs to merchant portal test/prod.
 *
 * @codingStandardsIgnoreStart
 */
enum MerchantPortal: string
{
    case TEST = 'https://merchantportal.integration.resurs.com/login';
    case PROD = 'https://merchantportal.resurs.com/login';
}
