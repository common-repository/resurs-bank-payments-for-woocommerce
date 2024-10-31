<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Widget;

use Resursbank\Ecom\Exception\FilesystemException;

/**
 * Basic widget functionality.
 */
class Widget
{
    /**
     * Get list of unique tag names in rendered content.
     *
     * This is useful to platforms requiring us to escape content we echo.
     */
    public static function getTagNames(string $content): array
    {
        $tagNames = [];

        preg_match_all(
            pattern: '/<([a-zA-Z0-9\-]+)\b[^>]*>/',
            subject: $content,
            matches: $tagNames
        );

        return isset($tagNames[1]) ? array_unique($tagNames[1]) : [];
    }

    /**
     * @throws FilesystemException
     */
    public function render(
        string $file
    ): string {
        ob_start();

        if (!file_exists(filename: $file)) {
            throw new FilesystemException(
                message: "Template file not found: $file"
            );
        }

        require $file;

        return (string) ob_get_clean();
    }
}
