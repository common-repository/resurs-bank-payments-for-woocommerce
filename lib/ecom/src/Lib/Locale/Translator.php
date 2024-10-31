<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Utilities\DataConverter;

use function file_get_contents;
use function is_string;
use function json_decode;

/**
 * Methods to extract language-specific phrases. The intention is to maintain
 * consistent terminology between implementations.
 *
 * @todo Check if ConfigException require test.
 */
abstract class Translator
{
    /**
     * Path to the translations file that holds all translations in Ecom.
     */
    private static string $translationsFilePath = __DIR__ . '/Resources/translations.json';

    /**
     * Key to store cached translations under.
     */
    private static string $cacheKey = 'resursbank-ecom-translations';

    /**
     * Loads translations file from disk, decodes the result into a collection
     * and returns that collection, and caches the resulting collection.
     *
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws IllegalValueException
     */
    public static function load(?string $translationFile = null): PhraseCollection
    {
        $translationFilePath = $translationFile ?? self::$translationsFilePath;

        if (!file_exists(filename: $translationFilePath)) {
            throw new FilesystemException(
                message: 'Translations file could not be found on path: ' .
                    self::$translationsFilePath,
                code: FilesystemException::CODE_FILE_MISSING
            );
        }

        $content = file_get_contents(filename: $translationFilePath);

        if (!is_string(value: $content) || $content === '') {
            throw new FilesystemException(
                message: 'Translation file ' . $translationFilePath .
                    ' is empty.',
                code: FilesystemException::CODE_FILE_EMPTY
            );
        }

        $result = self::decodeData(data: $content);

        Config::getCache()->write(
            key: self::getCacheKey(translationFile: $translationFile),
            data: json_encode(
                value: $result->toArray(),
                flags: JSON_THROW_ON_ERROR
            ),
            ttl: 3600
        );

        return $result;
    }

    /**
     * Takes an english phrase and translates it to the configured language.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @see Config::$language
     */
    public static function translate(
        string $phraseId,
        ?string $translationFile = null
    ): string {
        $phrases = self::getData(translationFile: $translationFile);
        $result = null;

        /** @var Phrase $item */
        foreach ($phrases as $item) {
            if ($item->id !== $phraseId) {
                continue;
            }

            /** @var string $result */
            $result = $item->translation->{Config::getLanguage()->value};
        }

        if ($result === null) {
            throw new TranslationException(
                message: "A translation with $phraseId could not be found."
            );
        }

        return $result;
    }

    /**
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws IllegalValueException
     */
    public static function getData(?string $translationFile = null): PhraseCollection
    {
        $cachedData = Config::getCache()->read(
            key: self::getCacheKey(translationFile: $translationFile)
        );

        return $cachedData === null
            ? self::load(translationFile: $translationFile)
            : self::decodeData(data: $cachedData);
    }

    /**
     * Decodes JSON data into a collection of phrases.
     *
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public static function decodeData(string $data): PhraseCollection
    {
        /** @var array $decode */
        $decode = json_decode(
            json: $data,
            associative: false,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        /** @var PhraseCollection $result */
        $result = DataConverter::arrayToCollection(
            data: $decode,
            type: Phrase::class
        );

        return $result;
    }

    /**
     * Generates a valid cache key which includes the name of the translation file.
     *
     * @todo preg_replace returns null|array|string, this method is required to return string ECP-372
     */
    public static function getCacheKey(?string $translationFile = null): string
    {
        $rawKey = ($translationFile ?
            (self::$cacheKey . '-' . $translationFile) :
            (self::$cacheKey . '-' . preg_replace(
                pattern: '/[0-9]/',
                replacement: '',
                subject: sha1(string: self::$translationsFilePath)
            ))
        );
        $key = preg_replace(
            pattern: '/[^a-zA-Z\d\-_]/',
            replacement: '-',
            subject: $rawKey
        );

        if (is_string(value: $key)) {
            return $key;
        }

        return '';
    }
}
