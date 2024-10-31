<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Unique Selling Point fetcher
 */
class UniqueSellingPoint extends Widget
{
    /** @var ReadMore */
    public readonly ReadMore $readMore;

    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $message;

    /**
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function __construct(
        public readonly PaymentMethod $paymentMethod,
        public readonly float $amount
    ) {
        $this->readMore = new ReadMore(
            paymentMethod: $this->paymentMethod,
            amount: $amount
        );
        $this->message = $this->getBasicTranslation(
            paymentMethodType: $this->paymentMethod->type
        );
        $this->content = $this->render(
            file: __DIR__ . '/unique-selling-point.phtml'
        );
    }

    /**
     * Fetches the localized USP translation for a payment method type.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws IllegalValueException
     */
    public function getBasicTranslation(Type $paymentMethodType): string
    {
        $return = Translator::translate(
            phraseId: str_replace(
                '_',
                '-',
                strtolower($paymentMethodType->value)
            ),
            translationFile: __DIR__ . '/Resources/translations.json'
        );
        return $return !== 'N/A' ? $return : '';
    }
}
