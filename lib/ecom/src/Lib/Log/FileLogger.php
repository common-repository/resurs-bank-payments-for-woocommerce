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
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\FormatException;
use Throwable;

/**
 * Write logfiles to disk.
 */
class FileLogger implements LoggerInterface
{
    private const LOG_FILENAME = 'ecom.log';
    private const PATH_ERR_EMPTY = 'Specified log file path is empty';
    private const PATH_ERR_WHITESPACE = 'Specified log file path has trailing or leading whitespace';
    private const PATH_ERR_TRAILING_SEPARATOR = 'Specified log file path has a trailing directory separator character';
    private const PATH_ERR_NOT_DIRECTORY = 'Specified log file path is not a directory';
    private const PATH_ERR_FILE_NOT_WRITABLE = 'Specified log file path is not writable';
    private const WRITE_ERROR = 'No data was written to the log file';
    private const ERR_UNWRITABLE = 'Log file appears to be unwritable';

    /**
     * @param string $path Path to log file storage directory.
     * @throws FilesystemException
     * @throws EmptyValueException
     * @throws FormatException
     */
    public function __construct(
        private readonly string $path
    ) {
        $this->validatePath();
        $this->validateLogFile();
    }

    /**
     * Logs message with log level DEBUG
     *
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function debug(string|Throwable $message): void
    {
        $this->log(level: LogLevel::DEBUG, message: $message);
    }

    /**
     * Logs message with log level INFO
     *
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function info(string|Throwable $message): void
    {
        $this->log(level: LogLevel::INFO, message: $message);
    }

    /**
     * Logs message with log level WARNING
     *
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function warning(string|Throwable $message): void
    {
        $this->log(level: LogLevel::WARNING, message: $message);
    }

    /**
     * Logs message with log level ERROR
     *
     * @throws ConfigException
     * @throws FilesystemException
     */
    public function error(string|Throwable $message): void
    {
        $this->log(level: LogLevel::ERROR, message: $message);
    }

    /**
     * Write log entry to file on disk.
     *
     * @throws FilesystemException
     * @throws ConfigException
     */
    private function log(LogLevel $level, string|Throwable $message): void
    {
        $this->validateLogFile();

        if ($message instanceof Throwable) {
            $this->logError(error: $message);
        } elseif (LogLevel::loggable(level: $level)) {
            $date = (new DateTime())->format(format: 'c');

            if (
                !file_put_contents(
                    filename: $this->getFilename(),
                    data: $date . ' ' . $level->name . ': ' . $message . PHP_EOL,
                    flags: FILE_APPEND | LOCK_EX
                )
            ) {
                throw new FilesystemException(message: self::WRITE_ERROR);
            }
        }
    }

    /**
     * Log Error object by converting it to a string and feeding it to the log method.
     *
     * @throws ConfigException
     * @throws FilesystemException
     */
    private function logError(Throwable|Error $error): void
    {
        $this->log(
            level: LogLevel::ERROR,
            message: $error->getMessage() . ', ' . $error->getTraceAsString()
        );
    }

    /**
     * Returns absolute path to log file.
     */
    private function getFilename(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . self::LOG_FILENAME;
    }

    /**
     * Validate logfile storage path.
     *
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws FormatException
     */
    private function validatePath(): void
    {
        if ($this->path === '') {
            throw new EmptyValueException(message: self::PATH_ERR_EMPTY);
        }

        if ($this->path !== trim(string: $this->path)) {
            throw new FormatException(message: self::PATH_ERR_WHITESPACE);
        }

        if (substr(string: $this->path, offset: -1) === DIRECTORY_SEPARATOR) {
            throw new FormatException(
                message: self::PATH_ERR_TRAILING_SEPARATOR
            );
        }

        if (!is_dir(filename: $this->path)) {
            throw new FilesystemException(
                message: self::PATH_ERR_NOT_DIRECTORY
            );
        }

        if (!is_writable(filename: $this->path)) {
            throw new FilesystemException(
                message: self::PATH_ERR_FILE_NOT_WRITABLE
            );
        }
    }

    /**
     * Validate existing log file if any.
     *
     * @throws FilesystemException
     */
    private function validateLogFile(): void
    {
        if (
            is_file(filename: $this->getFilename()) &&
            !is_writable(filename: $this->getFilename())
        ) {
            throw new FilesystemException(message: self::ERR_UNWRITABLE);
        }
    }
}
