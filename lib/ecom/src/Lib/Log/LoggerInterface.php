<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use Throwable;

/**
 * Contract for a logger implementation.
 */
interface LoggerInterface
{
    public function debug(string|Throwable $message): void;

    public function info(string|Throwable $message): void;

    public function warning(string|Throwable $message): void;

    public function error(string|Throwable $message): void;
}
