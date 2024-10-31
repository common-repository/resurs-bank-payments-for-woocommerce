<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentHistory\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\CollectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Result;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\PaymentHistory\Translator;

/**
 * Payment history log widget.
 */
class Log extends Widget
{
    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $css;

    /** @var string */
    public readonly string $js;

    /**
     * @throws FilesystemException
     */
    public function __construct(
        public readonly EntryCollection $entries
    ) {
        $this->content = $this->render(file: __DIR__ . '/log.phtml');
        $this->css = $this->render(file: __DIR__ . '/log.css');
        $this->js = $this->render(file: __DIR__ . '/log.js');
    }

    /**
     * Resolve title content.
     *
     * This is displayed above the entry table. It's intended to reflect
     * relating order/payment and environment.
     *
     * @throws CollectionException
     * @throws FilesystemException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws TranslationException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     */
    public function getWidgetTitle(): string
    {
        $entry = $this->entries->current();

        return sprintf(
            Translator::translate(phraseId: 'widget-title'),
            $entry instanceof Entry ?
                    ((string) $entry->reference !== '' ? $entry->reference : $entry->paymentId) :
                    '',
            Translator::translate(
                phraseId: Config::isProduction() ? 'production' : 'test'
            )
        );
    }

    /**
     * Escape and format extra data.
     */
    public function getExtraData(Entry $entry): string
    {
        return str_replace(
            search: ["\n", "\r"],
            replace: '',
            subject: nl2br(
                string: htmlspecialchars(
                    string: addslashes(string: (string) $entry->extra),
                    flags: ENT_QUOTES,
                    encoding: 'UTF-8'
                )
            )
        );
    }

    /**
     * Get row class based on entry result.
     */
    public function getResultClass(Entry $entry): string
    {
        return match ($entry->result) {
            Result::SUCCESS => 'success-entry',
            Result::ERROR => 'error-entry',
            Result::INFO => 'info-entry'
        };
    }

    /**
     * If the extra content is shorter than 40 characters, hide extra button.
     *
     * The extra content will instead be displayed directly in the table column.
     */
    public function showExtraBtn(Entry $entry): bool
    {
        return $entry->extra !== null && strlen(string: $entry->extra) > 40;
    }

    /**
     * Resolve content for user column as "Entry.user (Entry.userReference)"
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function getUser(Entry $entry): string
    {
        $result = Translator::translate(phraseId: $entry->user->value);

        if ((string) $entry->userReference !== '') {
            $result .= " ($entry->userReference)";
        }

        return $result;
    }
}
