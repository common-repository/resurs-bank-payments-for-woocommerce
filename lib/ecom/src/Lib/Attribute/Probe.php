<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Attribute\Probe\Probable;
use SplFileInfo;

/**
 * Generic functionality for cache repository implementations.
 */
class Probe
{
    /**
     * Scan through a directory tree, resolving all Probable classes.
     *
     * @throws FilesystemException
     * @phpcsSuppress SlevomatCodingStandard.Complexity.Cognitive
     */
    public static function getClasses(string $dir): array
    {
        $result = [];

        $path = Config::getPath(dir: $dir);

        self::validatePath(path: $path);

        $iterator = new RecursiveDirectoryIterator(
            directory: $path,
            flags: FilesystemIterator::SKIP_DOTS
        );

        foreach (new RecursiveIteratorIterator(iterator: $iterator) as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            $class = self::getClassname(file: $file);

            if ($class === null) {
                continue;
            }

            $result[] = $class;
        }

        return $result;
    }

    /**
     * @throws FilesystemException
     */
    public static function validatePath(
        string $path
    ): void {
        if (!is_dir(filename: $path)) {
            throw new FilesystemException(message: "Not a directory $path");
        }
    }

    /**
     * Check that PHP class exists.
     */
    public static function validateClass(string $class): bool
    {
        if (!class_exists(class: $class)) {
            return false;
        }

        $reflection = new ReflectionClass(objectOrClass: $class);

        return !empty($reflection->getAttributes(name: Probable::class));
    }

    /**
     * Get classname from $file.
     */
    public static function getClassname(SplFileInfo $file): ?string
    {
        if ($file->isDir() || $file->getExtension() !== 'php') {
            return null;
        }

        $classPath = str_replace(
            search: Config::getPath(),
            replace: '',
            subject: $file->getPath()
        );

        if (str_starts_with(haystack: $classPath, needle: '/src/')) {
            $classPath = substr_replace(
                string: $classPath,
                replace: 'Ecom',
                offset: 0,
                length: 5
            );
        }

        if (str_starts_with(haystack: $classPath, needle: '/tests/')) {
            $classPath = substr_replace(
                string: $classPath,
                replace: 'EcomTest',
                offset: 0,
                length: 6
            );
        }

        $class = 'Resursbank\\' . str_replace(
            search: '/',
            replace: '\\',
            subject: $classPath
        ) . '\\' . $file->getBasename(suffix: '.' . $file->getExtension());

        return self::validateClass(class: $class) ? $class : null;
    }
}
