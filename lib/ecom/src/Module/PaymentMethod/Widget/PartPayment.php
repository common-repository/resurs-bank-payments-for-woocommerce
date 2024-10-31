<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\Validation\MissingKeyException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\AnnuityFactor\AnnuityInformation;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PriceSignage\Cost;
use Resursbank\Ecom\Lib\Utilities\Price;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\AnnuityFactor\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;
use Resursbank\Ecom\Module\PriceSignage\Repository as SignageRepository;
use Throwable;

use function max;
use function sprintf;

/**
 * Renders Part payment widget HTML and CSS
 */
class PartPayment extends Widget
{
    /** @var Cost */
    public readonly Cost $cost;

    /** @var string */
    public readonly string $logo;

    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $css;

    /** @var string */
    public readonly string $js;

    /**
     * @param string $fetchStartingCostUrl | URL in implementation used to fetch
     * starting cost for the part payment widget as the configuration of the
     * product / cart changes where this widget is used. The endpoint must sit
     * in your implementation, the JS method which uses this method can then
     * be called to fetch the starting cost (see the template of this widget).
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws MissingKeyException
     * @throws ReflectionException
     * @throws Throwable
     * @throws TranslationException
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $storeId,
        public readonly PaymentMethod $paymentMethod,
        public readonly int $months,
        public readonly float $amount,
        public readonly string $currencySymbol,
        public readonly CurrencyFormat $currencyFormat,
        public readonly string $fetchStartingCostUrl,
        public readonly int $decimals = 2,
        public readonly bool $displayInfoText = true,
        public readonly float $threshold = 0.0
    ) {
        $this->cost = $this->getCost();
        $this->logo = (string) file_get_contents(
            filename: __DIR__ . '/resurs.svg'
        );
        $this->content = $this->render(file: __DIR__ . '/part-payment.phtml');
        $this->css = $this->render(file: __DIR__ . '/part-payment.css');
        $this->js = $this->render(file: __DIR__ . '/part-payment.js.phtml');
    }

    /**
     * Fetches translated and formatted "Starting at %1 per month..." string
     * inside span element.
     *
     * @throws ConfigException
     */
    public function getStartingAt(): string
    {
        if (!$this->isEligible()) {
            return $this->getNotEligibleMessage();
        }

        try {
            return str_replace(
                search: ['%1', '%2'],
                replace: [
                    $this->getFormattedStartingAtCost(),
                    $this->getAnnuityInformation()->paymentPlanName,
                ],
                subject: Translator::translate(phraseId: 'starting-at')
            );
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return '';
        }
    }

    /**
     * Check whether the current cost is eligible for part payment.
     */
    public function isEligible(): bool
    {
        return
            $this->threshold === 0.0 ||
            $this->cost->monthlyCost >= $this->threshold;
    }

    /**
     * @throws ConfigException
     */
    public function getNotEligibleMessage(): string
    {
        $result = '';
        $period = $this->getLongestPeriodWithZeroInterest();

        if ($period === 0) {
            return $result;
        }

        try {
            $result = sprintf(
                Translator::translate('rb-pp-not-eligible'),
                $period
            );
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
        }

        return $result;
    }

    /**
     * Find the longest period with zero interest. If no such period exists,
     * return 0.
     *
     * @throws ConfigException
     */
    public function getLongestPeriodWithZeroInterest(): int
    {
        try {
            $annuityFactors = Repository::getAnnuityFactors(
                paymentMethodId: $this->paymentMethod->id
            );
        } catch (Throwable $e) {
            Config::getLogger()->error(message: $e);
            return 0;
        }

        $longestPeriod = 0;

        /** @var AnnuityInformation $annuityFactor */
        foreach ($annuityFactors as $annuityFactor) {
            if ($annuityFactor->interest > 0.0) {
                continue;
            }

            $longestPeriod = max(
                $annuityFactor->durationMonths,
                $longestPeriod
            );
        }

        return $longestPeriod;
    }

    public function getMonthlyCost(): float
    {
        return $this->cost->monthlyCost ?? 0;
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws MissingKeyException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     * @throws Throwable
     */
    private function getAnnuityInformation(): AnnuityInformation
    {
        $annuityFactors = Repository::getAnnuityFactors(
            paymentMethodId: $this->paymentMethod->id
        );

        /** @var AnnuityInformation $annuityFactor */
        foreach ($annuityFactors as $annuityFactor) {
            if ($annuityFactor->durationMonths === $this->months) {
                return $annuityFactor;
            }
        }

        throw new MissingKeyException(
            message: 'Could not find matching payment plan'
        );
    }

    /**
     * Fetch a Cost object from the Price signage API
     *
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     * @throws Throwable
     */
    private function getCost(): Cost
    {
        $costs = SignageRepository::getPriceSignage(
            paymentMethodId: $this->paymentMethod->id,
            amount: $this->amount,
            monthFilter: $this->months
        );

        if (empty($costs->costList->toArray())) {
            throw new EmptyValueException(
                message: 'Returned CostCollection appears to be empty'
            );
        }

        if (sizeof($costs->costList) > 1) {
            throw new IllegalValueException(
                message: 'Returned CostCollection contains more than one Cost'
            );
        }

        return array_values(array: $costs->costList->toArray())[0];
    }

    /**
     * Fetches formatted starting at cost with currency symbol.
     */
    private function getFormattedStartingAtCost(): string
    {
        return Price::format(
            value: $this->cost->monthlyCost,
            decimals: $this->decimals
        );
    }
}
