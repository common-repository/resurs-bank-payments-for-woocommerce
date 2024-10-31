<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities;

use Exception;
use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;

use function dirname;
use function is_object;
use function is_string;

/**
 * Generic Utils Class for things that is good to have.
 *
// phpcs:ignore
 * @version 1.0.0
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @todo Refactor entire class. See ECP-351. Remember to remove phpcs:ignore below when done.
 * @todo There is a unit test that depends on the version annotation here. These annotations are however prohibited.
 */
// phpcs:ignore
class Generic
{
    /**
     * Internal errorhandler.
     *
     * @var callable|null
     */
    private $internalErrorHandler;

    private int $internalExceptionCode;

    /**
     * Error message on internal handled errors, if any.
     */
    private string $internalExceptionMessage = '';

    /**
     * If open_basedir-warnings has been triggered once, we store that here.
     *
     * @todo We should use our FS classes instead to check for readability.
     */
    private bool $openBaseDirExceptionTriggered = false;

    private object $composerData;

    private string $composerLocation;

    /**
     * @throws Exception
     */
    public function getComposerVendor(string $composerLocation): string
    {
        return $this->getNameEntry(
            part: 'vendor',
            composerLocation: $composerLocation
        );
    }

    /**
     * Extract a tag from composer.json.
     *
     * @throws Exception
     */
    public function getComposerTag(string $location, string $tag): string
    {
        $return = '';

        // @todo Object should be defined as stdClass or mor specific object.

        if (empty($this->composerData)) {
            $this->getComposerConfig(location: $location);
        }

        if (
            isset($this->composerData->{$tag}) &&
            is_string(value: $this->composerData->{$tag})
        ) {
            $return = (string) $this->composerData->{$tag};
        } elseif ($this->isOpenBaseDirException()) {
            $return = $this->getOpenBaseDirExceptionString();
        }

        return $return;
    }

    /**
     * @param string $location Location of composer.json.
     * @param int $maxDepth How deep the search for a composer.json will be. Usually you should not need more than 3.
     * @throws Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @todo Refactor, see ECP-350. Remember to remove phpcs:ignore below when done.
     */
    // phpcs:ignore
    public function getComposerConfig(string $location, int $maxDepth = 3): string
    {
        $this->setTemporaryInternalErrorHandler();

        if ($maxDepth > 3 || $maxDepth < 1) {
            $maxDepth = 3;
        }

        // Pre-check if file exists, to also make sure that open_basedir is not a problem.
        $locationCheck = file_exists(filename: $location);
        $this->isOpenBaseDirException();

        if (!$this->openBaseDirExceptionTriggered && !$locationCheck) {
            throw new FilesystemException(message: 'Invalid path', code: 1013);
        }

        if ($this->isOpenBaseDirException()) {
            return $this->getOpenBaseDirExceptionString();
        }

        $startAt = dirname(path: $location);

        if ($this->hasComposerFile(location: $startAt)) {
            $this->getComposerConfigData(location: $startAt);
            return $startAt;
        }

        $composerLocation = null;

        while ($maxDepth--) {
            $startAt .= '/..';

            if ($this->hasComposerFile(location: $startAt)) {
                $composerLocation = $startAt;
                break;
            }
        }

        if ($composerLocation === null) {
            throw new IllegalValueException(message: 'No composer.json found');
        }

        $this->getComposerConfigData(location: $composerLocation);

        return $this->composerLocation;
    }

    /**
     * Using both class and composer.json to discover version (in case that composer.json are removed in a "final").
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function getVersionByAny(
        string $composerLocation = '',
        int $composerDepth = 3,
        string $className = ''
    ): string {
        $return = '';

        $byComposer = $this->getVersionByComposer(
            location: $composerLocation,
            maxDepth: $composerDepth
        );
        $byClass = $this->getVersionByClassDoc(className: $className);

        // Composer always have higher priority.
        if (!empty($byComposer)) {
            $return = $byComposer;
        } elseif (!empty($byClass)) {
            $return = $byClass;
        }

        return $return;
    }

    /**
     * @param int $maxDepth Default is 3.
     * @throws Exception
     */
    public function getVersionByComposer(string $location, int $maxDepth = 3): string
    {
        $return = '';

        if (
            !empty($this->getComposerConfig(
                location: $location,
                maxDepth: $maxDepth
            ))
            && !$this->isOpenBaseDirException()
        ) {
            $return = $this->getComposerTag(
                location: $this->composerLocation,
                tag: 'version'
            );
        } elseif ($this->isOpenBaseDirException()) {
            $return = $this->getOpenBaseDirExceptionString();
        }

        return $return;
    }

    /**
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function getVersionByClassDoc(string $className = ''): string
    {
        return $this->getDocBlockItem(item: '@version', className: $className);
    }

    /**
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function getDocBlockItem(string $item, string $functionName = '', string $className = ''): string
    {
        return Generic\Docblock::getExtractedDocBlockItem(
            item: $item,
            doc: Generic\Docblock::getExtractedDocBlock(
                functionName: $functionName,
                className: $className
            )
        );
    }

    /**
     * @param string $part Defines which part of the vendor row you want (name or the vendor itself)
     * @param string $composerLocation Where composer.json are stored.
     * @throws Exception
     * @noinspection PhpSameParameterValueInspection
     */
    private function getNameEntry(string $part, string $composerLocation): string
    {
        $return = '';
        $composerNameEntry = explode(
            separator: '/',
            string: $this->getComposerTag(
                location: $composerLocation,
                tag: 'name'
            ),
            limit: 2
        );

        switch ($part) {
            case 'name':
                if (isset($composerNameEntry[1])) {
                    $return = $composerNameEntry[1];
                }

                break;

            case 'vendor':
                if (isset($composerNameEntry[0])) {
                    $return = $composerNameEntry[0];
                }

                break;

            default:
        }

        return $return;
    }

    /**
     * Temporarily sets an error handler in an attempt to catch notice-level errors related to open_basedir
     */
    private function setTemporaryInternalErrorHandler(): void
    {
        if ($this->internalErrorHandler !== null) {
            restore_error_handler();
        }

        $this->internalErrorHandler = set_error_handler(
            callback: function ($errNo, $errStr) {
                if (empty($this->internalExceptionMessage)) {
                    $this->internalExceptionCode = $errNo;
                    $this->internalExceptionMessage = $errStr;
                }

                restore_error_handler();
                return $errNo === 2 && str_contains(
                    haystack: $errStr,
                    needle: 'open_basedir'
                );
            },
            error_levels: E_WARNING
        );
    }

    /**
     * Checks internal warnings for open_basedir exceptions during runs.
     */
    private function isOpenBaseDirException(): bool
    {
        // If triggered once, skip checks.
        if ($this->openBaseDirExceptionTriggered) {
            return $this->openBaseDirExceptionTriggered;
        }

        $return = $this->hasInternalException() &&
            $this->internalExceptionCode === 2 &&
            str_contains(
                haystack: $this->internalExceptionMessage,
                needle: 'open_basedir'
            );

        if ($return) {
            $this->openBaseDirExceptionTriggered = true;
        }

        return $return;
    }

    /**
     * Check for internal exception.
     */
    private function hasInternalException(): bool
    {
        return !empty($this->internalExceptionMessage);
    }

    /**
     * Exception string that is used in several places that will mark up if the running methods have
     * had problems with open_basedir security.
     */
    private function getOpenBaseDirExceptionString(): string
    {
        return 'open_basedir security active';
    }

    /**
     * Check for composer file.
     */
    private function hasComposerFile(string $location): bool
    {
        $return = false;

        if (file_exists(filename: sprintf('%s/composer.json', $location))) {
            $return = true;
        }

        return $return;
    }

    /**
     * @throws JsonException
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    private function getComposerConfigData(string $location): void
    {
        $this->composerLocation = $location;

        $getFrom = sprintf('%s/composer.json', $location);

        if (!file_exists(filename: $getFrom)) {
            return;
        }

        $data = null;
        $json = file_get_contents(filename: $getFrom);

        if ($json !== false && $json !== '') {
            $data = json_decode(
                json: $json,
                associative: false,
                depth: 768,
                flags: JSON_THROW_ON_ERROR
            );
        }

        if (!is_object(value: $data)) {
            return;
        }

        $this->composerData = $data;
    }
}
