<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Interface\PaymentMethod as PaymentMethodInterface;
use Resursbank\Ecom\Lib\Model\PaymentMethod\LegalLinkCollection;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Validation\FloatValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Defines payment method entity.
 *
 * NOTE: All Exceptions from namespace Validation extends ValidationException.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class PaymentMethod extends Model implements PaymentMethodInterface
{
    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly Type $type,
        public readonly float $minPurchaseLimit,
        public readonly float $maxPurchaseLimit,
        public readonly float $minApplicationLimit,
        public readonly float $maxApplicationLimit,
        public readonly LegalLinkCollection $legalLinks,
        public readonly bool $enabledForLegalCustomer,
        public readonly bool $enabledForNaturalCustomer,
        public readonly bool $priceSignagePossible,
        public int $sortOrder = 0,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly FloatValidation $floatValidation = new FloatValidation()
    ) {
        $this->validateId();
        $this->validateName();
        $this->validateMinPurchaseLimit();
        $this->validateMaxPurchaseLimit();
        $this->validateMinApplicationLimit();
        $this->validateMaxApplicationLimit();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMinLimit(): float
    {
        return $this->minPurchaseLimit;
    }

    public function getMaxLimit(): float
    {
        return $this->maxPurchaseLimit;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * Checks if payment method is eligible for part payment
     */
    public function isPartPayment(): bool
    {
        return $this->type === Type::RESURS_PART_PAYMENT ||
            $this->type === Type::RESURS_REVOLVING_CREDIT ||
            $this->type === Type::RESURS_CARD;
    }

    /**
     * Checks if payment method is an internal Resurs payment method (rather than one provided by an external partner)
     */
    public function isResursMethod(): bool
    {
        return str_starts_with(haystack: $this->type->name, needle: 'RESURS_');
    }

    public function enabledForB2b(): bool
    {
        return $this->enabledForLegalCustomer;
    }

    public function enabledForB2c(): bool
    {
        return $this->enabledForNaturalCustomer;
    }

    public function isInternal(): bool
    {
        return str_starts_with(haystack: $this->type->value, needle: 'RESURS_');
    }

    public function getTypeValue(): string
    {
        return $this->type->value;
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validateId(): void
    {
        $this->stringValidation->notEmpty(value: $this->id);
        $this->stringValidation->isUuid(value: $this->id);
    }

    /**
     * @throws EmptyValueException
     * @todo Add charset validation.
     */
    private function validateName(): void
    {
        $this->stringValidation->notEmpty(value: $this->name);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateMinPurchaseLimit(): void
    {
        $this->floatValidation->isPositive(value: $this->minPurchaseLimit);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateMaxPurchaseLimit(): void
    {
        $this->floatValidation->isPositive(value: $this->maxPurchaseLimit);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateMinApplicationLimit(): void
    {
        $this->floatValidation->isPositive(value: $this->minApplicationLimit);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateMaxApplicationLimit(): void
    {
        $this->floatValidation->isPositive(value: $this->maxApplicationLimit);
    }
}
