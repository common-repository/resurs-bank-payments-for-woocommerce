<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Callback\Enum\Action;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Implementation of Management callback data.
 */
class Management extends Model implements CallbackInterface
{
    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly Action $action,
        public readonly string $actionId,
        public readonly string $created,
        public readonly ?string $checkoutId = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validatePaymentId();
        $this->validateActionId();
        $this->validateCreated();
    }

    /**
     * Property wrapper to fulfill contract.
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * Property wrapper to fulfill contract.
     */
    public function getCheckoutId(): ?string
    {
        return $this->checkoutId;
    }

    /**
     * Get note explaining what happened.
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws TranslationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function getNote(): string
    {
        return sprintf(
            Translator::translate(phraseId: 'management-callback-received'),
            $this->action->value
        );
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validatePaymentId(): void
    {
        $this->stringValidation->notEmpty(value: $this->paymentId);
        $this->stringValidation->isUuid(value: $this->paymentId);
    }

    /**
     * @throws EmptyValueException|IllegalValueException
     */
    private function validateActionId(): void
    {
        $this->stringValidation->notEmpty(value: $this->actionId);
        $this->stringValidation->isUuid(value: $this->actionId);
    }

    /**
     * @throws EmptyValueException|IllegalValueException
     */
    private function validateCreated(): void
    {
        $this->stringValidation->notEmpty(value: $this->created);
        $this->stringValidation->isTimestampDate(value: $this->created);
    }
}
