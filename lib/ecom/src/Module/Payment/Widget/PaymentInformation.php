<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Payment\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Payment;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\Payment\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Throwable;

/**
 * Renders Payment Information widget for use in admin panel order view
 */
class PaymentInformation extends Widget
{
    /**
     * This is over-written by other implementations extending this class.
     */
    public const PAYMENT_ID_LABEL = 'payment-id';

    /** @var Payment */
    public readonly Payment $payment;

    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $css;

    /** @var string */
    public readonly string $logo;

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws ApiException
     * @throws AuthException
     * @throws ConfigException
     * @throws CurlException
     * @throws FilesystemException
     * @throws ValidationException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly string $currencySymbol,
        public readonly CurrencyFormat $currencyFormat
    ) {
        $this->payment = Repository::get(paymentId: $this->paymentId);
        $this->renderWidget();
    }

    /**
     * Get CSS statically on demand.
     *
     * @throws EmptyValueException
     */
    public static function getCss(): string
    {
        $css = file_get_contents(
            filename: __DIR__ . '/payment-information.css'
        );

        if (!$css) {
            throw new EmptyValueException(
                message: 'Failed to load stylesheet.'
            );
        }

        return $css;
    }

    public function hasAddress(): bool
    {
        return $this->payment->customer->deliveryAddress !== null;
    }

    public function getAddressRow2(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->addressRow2;
    }

    public function getAddressRow1(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->addressRow1;
    }

    public function getCity(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->postalArea;
    }

    public function getCountryCode(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->countryCode?->value;
    }

    public function getPostalCode(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->postalCode;
    }

    public function getStatus(): string
    {
        $result = $this->payment->status->value;

        $reason = str_replace(
            search: '_',
            replace: '-',
            subject: strtolower(
                string: (string)$this->payment->rejectedReason?->category?->value
            )
        );

        if ($reason !== '') {
            try {
                $result .= ' (' . Translator::translate(
                    phraseId: "reject-reason-$reason"
                ) . ')';
            } catch (Throwable) {
                // In case we get translation problems with nonexistent phrases.
                $result .= ' (' . sprintf('reject-reason-%s', $reason) . ')';
            }
        }

        return $result;
    }

    public function getPaymentMethodName(): string
    {
        return (string) $this->payment->paymentMethod?->name;
    }

    public function getCustomerName(): string
    {
        return (string) $this->payment->customer->deliveryAddress?->fullName;
    }

    public function getTelephone(): string
    {
        return $this->payment->customer->mobilePhone ?? '';
    }

    public function getEmail(): string
    {
        return $this->payment->customer->email ?? '';
    }

    public function getAuthorizedAmount(): float
    {
        return (float) $this->payment->order?->authorizedAmount;
    }

    public function getCapturedAmount(): float
    {
        return (float) $this->payment->order?->capturedAmount;
    }

    public function getRefundedAmount(): float
    {
        return (float) $this->payment->order?->refundedAmount;
    }

    public function getCancelledAmount(): float
    {
        return (float) $this->payment->order?->canceledAmount;
    }

    /**
     * Take supplied amount value and format with currency symbol etc.
     */
    public function getFormattedAmount(float $amount): string
    {
        return Price::format(
            value: $amount,
            decimals: 2,
            decimalSeparator: ',',
            thousandsSeparator: ' '
        );
    }

    /**
     * Get TD element with inline CSS.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getTdElement(
        string $content,
        bool $isHeader = false
    ): string {
        return '<td' .
            ($isHeader ? ' class="rb-pi-row-header"' : '') . '>' .
            ($isHeader ? Translator::translate(phraseId: $content) : $content)
            . '</td>';
    }

    /**
     * Get TR element containing two TD elements using this structure:
     *
     * <tr>
     *     <td>[TITLE]</td>
     *     <td>[CONTENT]</td>
     * </tr>
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function getTrElement(
        string $title,
        string $content
    ): string {
        return '<tr>' .
            $this->getTdElement(content: $title, isHeader: true) .
            $this->getTdElement(content: $content) . '</tr>';
    }

    /**
     * Assemble address data and separate with <br />
     */
    public function getAddressContent(): string
    {
        $data = [
            $this->getAddressRow1()
        ];

        $addressRow2 = $this->getAddressRow2();

        if ($addressRow2 !== '') {
            $data[] = $addressRow2;
        }

        $data[] = $this->getCity();

        $country = $this->getCountryCode();

        $data[] = $country . ($country !== '' ? ' - ' : '') . $this->getPostalCode();

        return implode(separator: '<br />', array: $data);
    }

    /**
     * Render widget components (kept in separate method, so it can be executed
     * from subclasses because the constructor defines the resource to be used).
     *
     * @throws EmptyValueException
     * @throws FilesystemException
     */
    protected function renderWidget(): void
    {
        $logo = file_get_contents(filename: __DIR__ . '/resurs.svg');

        if (!$logo) {
            throw new EmptyValueException(
                message: 'Failed to load logo image data'
            );
        }

        /* @phpstan-ignore-next-line */
        $this->logo = $logo ?? '';

        /* @phpstan-ignore-next-line */
        $this->content = $this->render(
            file: __DIR__ . '/payment-information.phtml'
        );

        // Render CSS.
        $css = file_get_contents(
            filename: __DIR__ . '/payment-information.css'
        );

        if (!$css) {
            throw new EmptyValueException(
                message: 'Failed to load stylesheet.'
            );
        }

        /* @phpstan-ignore-next-line */
        $this->css = self::getCss();
    }
}
