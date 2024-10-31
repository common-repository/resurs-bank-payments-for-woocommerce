<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Network\Curl;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Throwable;

/**
 * Converter to turn property names like "customer.mobilePhone" into more user-friendly strings like "mobile phone"
 */
abstract class ErrorTranslator
{
    /**
     * Gets converted and localized string.
     *
     * @throws ConfigException
     */
    public static function get(string $errorMessage): string
    {
        $errorMessage = self::reformatMessage(message: $errorMessage);

        try {
            return Translator::translate(
                phraseId: $errorMessage,
                translationFile: __DIR__ . '/Resources/errors.json'
            );
        } catch (Throwable $error) {
            Config::getLogger()->error(message: $error);
            return $errorMessage;
        }
    }

    /**
     * Reformat error message to match translator's format for IDs.
     *
     * Converts string to lowercase, removes any leading or trailing
     * non-alphanumeric characters and replace all other non-alphanumerics
     * with dashes.
     */
    private static function reformatMessage(string $message): string
    {
        // lowercase
        $message = strtolower(string: $message);

        $message = (string)preg_replace(
            pattern: '/^[^a-z0-9]/',
            replacement: '',
            subject: $message
        );
        $message = (string)preg_replace(
            pattern: '/[^a-z0-9]$/',
            replacement: '',
            subject: $message
        );
        return (string)preg_replace(
            pattern: '/[^a-z0-9\-:]/',
            replacement: '-',
            subject: $message
        );
    }
}
