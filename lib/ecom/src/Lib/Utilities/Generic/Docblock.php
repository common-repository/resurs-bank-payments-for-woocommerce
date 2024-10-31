<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities\Generic;

use ReflectionClass;
use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;

/**
 * Docblock-related helper methods for Generic.
 */
class Docblock
{
    /**
     * Extract docblock item.
     *
     * @todo Refactor, see ECP-352. Remember to remove phpcs:ignore below when done.
     */
    // phpcs:ignore
    public static function getExtractedDocBlockItem(string $item, string $doc): string
    {
        $return = '';

        if (!empty($doc)) {
            $docBlock = [];

            preg_match_all(
                pattern: sprintf('/%s\s(\w.+)\n/s', $item),
                subject: $doc,
                matches: $docBlock
            );

            if (isset($docBlock[1][0])) {
                $return = $docBlock[1][0];

                // Strip stuff after line breaks
                if (preg_match(pattern: '/[\n\r]/', subject: $return)) {
                    $multiRowData = preg_split(
                        pattern: '/[\n\r]/',
                        subject: $return
                    );

                    if ($multiRowData !== false) {
                        $return = $multiRowData[0] ?? '';
                    }
                }
            }
        }

        return $return;
    }

    /**
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public static function getExtractedDocBlock(
        string $functionName,
        string $className = ''
    ): string {
        if ($className === '') {
            $className = self::class;
        }

        if (!class_exists(class: $className)) {
            throw new IllegalValueException(
                message: "Class $className does not exist"
            );
        }

        $doc = new ReflectionClass(objectOrClass: $className);

        return $functionName === '' ?
            (string) $doc->getDocComment() :
            (string) $doc->getMethod(name: $functionName)->getDocComment();
    }
}
