<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Cache;

use JsonException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\ValidationException;

/**
 * Basic filesystem caching.
 *
 * @todo If we add a health report as discussed we should add some writ- / readable information about the cache dir /
 * @todo files since read will fail silently.
 */
class Filesystem extends AbstractCache implements CacheInterface
{
    /**
     * @param string $path | Directory where cache files will be stored.
     */
    public function __construct(
        private readonly string $path
    ) {
    }

    /**
     * If there should be any problem with the requested cache file, for example
     * if the file exists but isn't writable, its path is allocated by a
     * directory, its content is invalid or corrupt etc. this method will simply
     * return null, meaning it will fail silently.
     *
     * @throws ValidationException
     * @throws ConfigException
     * @todo Consider adding logs.
     */
    public function read(string $key): ?string
    {
        // Make sure the key consists of valid characters.
        $this->validateKey(key: $key);

        // Read and parse cache file.
        $entry = $this->decodeEntry(
            data: $this->getFileContent(file: $this->getFile(key: $key))
        );

        return (
            $entry !== null &&
            $this->validate(key: $key, entry: $entry)
        ) ? $entry->data : null;
    }

    /**
     * @throws ValidationException
     * @throws FilesystemException
     * @throws JsonException
     */
    public function write(string $key, string $data, int $ttl): void
    {
        // Make sure the key consists of valid characters.
        $this->validateKey(key: $key);

        // Create the cache directory, if it's missing.
        $this->createPath();

        // Create the cache file.
        $filename = $this->getFile(key: $key);

        if (file_exists(filename: $filename)) {
            if (!is_file(filename: $filename)) {
                throw new FilesystemException(
                    message: "$filename is not a file."
                );
            }

            if (!is_writable(filename: $filename)) {
                throw new FilesystemException(
                    message: "$filename is not writable."
                );
            }
        }

        file_put_contents(
            filename: $filename,
            data: $this->encodeEntry(data: $data, ttl: $ttl)
        );
    }

    /**
     * @throws FilesystemException
     * @throws ValidationException
     */
    public function clear(string $key): void
    {
        // Make sure the key consists of valid characters.
        $this->validateKey(key: $key);

        // Read cache file.
        $file = $this->getFile(key: $key);

        if (!file_exists(filename: $file)) {
            return;
        }

        if (!is_file(filename: $file)) {
            throw new FilesystemException(
                message: "$file is not a regular file."
            );
        }

        if (!is_writable(filename: $file)) {
            throw new FilesystemException(message: "$file is not writable.");
        }

        unlink(filename: $file);
    }

    /**
     * Prepare directory where cache is stored by creating it if it doesn't
     * already exist and making sure it's writable.
     *
     * @throws FilesystemException
     */
    private function createPath(): void
    {
        if (is_file(filename: $this->path)) {
            throw new FilesystemException(message: "$this->path is a file.");
        }

        if (
            !is_dir(filename: $this->path) &&
            !mkdir(
                directory: $this->path,
                permissions: 0755,
                recursive: true
            ) &&
            !is_dir(filename: $this->path)
        ) {
            throw new FilesystemException(
                message: "Failed to create directory $this->path"
            );
        }

        if (!is_writable(filename: $this->path)) {
            throw new FilesystemException(
                message: "$this->path is not writable."
            );
        }
    }

    /**
     * Convert key to cache file path.
     */
    private function getFile(string $key): string
    {
        return "$this->path/$key.cache";
    }

    /**
     * Get content from cache file.
     */
    private function getFileContent(
        string $file
    ): string {
        $result = '';

        if (
            file_exists(filename: $file) &&
            is_file(filename: $file) &&
            is_readable(filename: $file)
        ) {
            $result = (string) file_get_contents(filename: $file);
        }

        return $result;
    }
}
