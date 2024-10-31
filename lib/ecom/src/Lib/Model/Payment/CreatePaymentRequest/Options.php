<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Options\Callbacks;
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Options\RedirectionUrls;

/**
 * Application data for a payment.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Options extends Model
{
    /**
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly ?bool $initiatedOnCustomersDevice = null,
        public readonly ?bool $handleManualInspection = null,
        public readonly ?bool $handleFrozenPayments = null,
        public readonly bool $automaticCapture = false,
        public readonly ?RedirectionUrls $redirectionUrls = null,
        public readonly ?Callbacks $callbacks = null,
        #[IntValue(
            min: 1,
            max: 43200
        )] public readonly ?int $timeToLiveInMinutes = null
    ) {
        parent::__construct();
        $this->validateAutomaticCapture();
    }

    /**
     * @throws IllegalValueException
     */
    private function validateAutomaticCapture(): void
    {
        if ($this->handleFrozenPayments && $this->automaticCapture) {
            throw new IllegalValueException(
                message: 'automaticCapture cannot be set to true when handleFrozenPayments is set to true'
            );
        }
    }
}
