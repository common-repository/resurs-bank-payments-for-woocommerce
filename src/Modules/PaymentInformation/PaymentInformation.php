<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Woocommerce\Modules\PaymentInformation;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Module\Payment\Widget\PaymentInformation as EcomPaymentInformation;
use Resursbank\Woocommerce\Util\Admin;
use Resursbank\Woocommerce\Util\Currency;
use Resursbank\Woocommerce\Util\Sanitize;

/**
 * Handles the output of the order view payment information widget
 *
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 */
class PaymentInformation
{
    public EcomPaymentInformation $widget;

    /**
     * @param string $paymentId Resurs payment ID
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     * @throws AttributeCombinationException
     */
    public function __construct(string $paymentId)
    {
        $currencySymbol = Currency::getWooCommerceCurrencySymbol();
        $currencyFormat = Currency::getEcomCurrencyFormat();
        $this->widget = new EcomPaymentInformation(
            paymentId: $paymentId,
            currencySymbol: $currencySymbol,
            currencyFormat: $currencyFormat
        );
    }

    /**
     * Init method for loading module CSS.
     */
    public static function init(): void
    {
        add_action(
            'admin_head',
            'Resursbank\Woocommerce\Modules\PaymentInformation\PaymentInformation::setCss'
        );
    }

    /**
     * Sets CSS in header if the current page is the order view.
     *
     * @throws EmptyValueException
     */
    public static function setCss(): void
    {
        if (!Admin::isInShopOrderEdit()) {
            return;
        }

        echo '<style>' .
            Sanitize::sanitizeHtml(html: EcomPaymentInformation::getCss()) .
            '</style>';
    }

    /**
     * Outputs the actual widget HTML
     *
     * @noinspection PhpUnused
     */
    public function getWidget(): void
    {
        echo Sanitize::sanitizeHtml(html: $this->widget->content);
    }
}
