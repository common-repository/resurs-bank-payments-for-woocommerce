<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use Throwable;

/**
 * Gracefully disable logging.
 *
 * NOTE: Multiple methods ignore phpcs inspections in this class because they
 * should do nothing yet must be implemented to be compliant with LoggerInterface.
 */
class NoneLogger implements LoggerInterface
{
    /**
     * Doesn't initialize anything.
     */
    public function __construct()
    {
        // Do nothing.
    }

    /**
     * Handle debug level logs.
     */
    // phpcs:ignore
    public function debug(string|Throwable $message): void
    {
    }

    /**
     * Handle info level logs.
     */
    // phpcs:ignore
    public function info(string|Throwable $message): void
    {
    }

    /**
     * Handle warning level logs.
     */
    // phpcs:ignore
    public function warning(string|Throwable $message): void
    {
    }

    /**
     * Handle error level logs.
     */
    // phpcs:ignore
    public function error(string|Throwable $message): void
    {
    }
}
