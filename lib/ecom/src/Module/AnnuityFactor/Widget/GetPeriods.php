<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\AnnuityFactor\Widget;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\AnnuityFactor\AnnuityInformation;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Widget\Widget;
use Resursbank\Ecom\Module\AnnuityFactor\Repository;
use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;
use Throwable;

/**
 * Render JavaScript code to sync list of periods with selected payment method.
 */
class GetPeriods extends Widget
{
    /** @var string */
    public readonly string $js;

    /**
     * @param string|null $methodElementId Required when using standard widget
     * JavaScript functions to manage elements. See template.
     * @param string|null $periodElementId Required when using standard widget
     * JavaScript functions to manage elements. See template.
     * @throws FilesystemException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        public readonly string $storeId,
        public readonly ?string $methodElementId = null,
        public readonly ?string $periodElementId = null,
        public readonly bool $automatic = true,
        public readonly ?string $selectedPaymentMethod = null,
        public readonly ?string $selectedPeriod = null
    ) {
        $this->js = $this->render(file: __DIR__ . '/get-periods.js.phtml');
    }

    /**
     * Fetch annuity factors for each payment method and add them to the
     * resulting array. Each payment method defines an inner array with the
     * annuity factors for that payment method, keyed by the period.
     */
    public function getJsonData(): string
    {
        try {
            $result = [];
            $methods = PaymentMethodRepository::getPaymentMethods();

            /** @var PaymentMethod $method */
            foreach ($methods as $method) {
                $result[$method->getId()] = $this->getAnnuityFactorsForMethod(
                    $method
                );
            }

            return json_encode(value: $result, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $error) {
            try {
                Config::getLogger()->error($error);
            } catch (ConfigException) {
                // Do nothing.
            }
        }

        return "{}";
    }

    /**
     * Fetch payment method IDs and names.
     */
    public function getJsonPaymentMethods(): string
    {
        try {
            $result = [];
            $methods = Repository::filterMethods(
                PaymentMethodRepository::getPaymentMethods()
            );

            /** @var PaymentMethod $method */
            foreach ($methods as $method) {
                $result[$method->getId()] = [
                    'id' => $method->getId(),
                    'name' => $method->getName()
                ];
            }

            return json_encode(value: $result, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $error) {
            try {
                Config::getLogger()->error($error);
            } catch (ConfigException) {
                // Do nothing.
            }
        }

        return '{}';
    }

    /**
     * Resolve annuity factors for a specific payment method.
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
     */
    private function getAnnuityFactorsForMethod(PaymentMethod $method): array
    {
        $result = [];
        $annuityFactors = Repository::getAnnuityFactors(
            paymentMethodId: $method->getId()
        );

        /** @var AnnuityInformation $annuityFactor */
        foreach ($annuityFactors as $annuityFactor) {
            $result[$annuityFactor->durationMonths] = $annuityFactor->paymentPlanName;
        }

        return $result;
    }
}
