<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Api;

/**
 * API scope types.
 *
 * @codingStandardsIgnoreStart
 */
enum GrantType: string
{
    case CREDENTIALS = 'client_credentials';
}
