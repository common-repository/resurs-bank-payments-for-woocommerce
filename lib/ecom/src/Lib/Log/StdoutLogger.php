<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Log;

use DateTime;
use Error;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\IOException;
use Throwable;

/**
 * Write logs directly to STDOUT and STDERR
 */
class StdoutLogger implements LoggerInterface
{
    private const ERR_GENERAL_WRITE = 'Unable to write message to STDOUT/STDERR';

    /**
     * @throws IOException
     * @throws ConfigException
     */
    public function debug(string|Throwable $message): void
    {
        $this->log(level: LogLevel::DEBUG, message: $message);
    }

    /**
     * @throws IOException
     * @throws ConfigException
     */
    public function info(string|Throwable $message): void
    {
        $this->log(level: LogLevel::INFO, message: $message);
    }

    /**
     * @throws IOException
     * @throws ConfigException
     */
    public function warning(string|Throwable $message): void
    {
        $this->log(level: LogLevel::WARNING, message: $message);
    }

    /**
     * @throws IOException
     * @throws ConfigException
     */
    public function error(string|Throwable $message): void
    {
        $this->log(level: LogLevel::ERROR, message: $message);
    }

    /**
     * Write log entry to STDOUT/STDERR (depending on log level)
     *
     * @throws ConfigException
     * @throws IOException
     */
    private function log(LogLevel $level, string|Throwable $message): void
    {
        if ($message instanceof Error) {
            $this->logError(error: $message);
        } elseif ($message instanceof Throwable) {
            $this->logException(exception: $message);
        } elseif (LogLevel::loggable(level: $level)) {
            $fileHandle = match ($level) {
                LogLevel::EXCEPTION, LogLevel::ERROR => fopen(
                    filename: 'php://stderr',
                    mode: 'ab'
                ),
                LogLevel::DEBUG, LogLevel::INFO, LogLevel::WARNING => fopen(
                    filename: 'php://stdout',
                    mode: 'ab'
                )
            };

            if ($fileHandle === false) {
                throw new IOException(message: self::ERR_GENERAL_WRITE);
            }

            $date = (new DateTime())->format(format: 'c');
            $formattedMessage = $date . ' ' . $level->name . ': ' . $message;

            fwrite(stream: $fileHandle, data: $formattedMessage);
            fclose(stream: $fileHandle);
        }
    }

    /**
     * Log Exception object by converting it to a string and feeding it to the log method.
     *
     * @throws ConfigException
     * @throws IOException
     */
    private function logException(Throwable $exception): void
    {
        $this->log(
            level: LogLevel::EXCEPTION,
            message: $exception->getTraceAsString()
        );
    }

    /**
     * Log Error object by converting it to a string and feeding it to the log method.
     *
     * @throws IOException
     * @throws ConfigException
     */
    private function logError(Throwable $error): void
    {
        $this->log(
            level: LogLevel::ERROR,
            message: $error->getTraceAsString()
        );
    }
}
