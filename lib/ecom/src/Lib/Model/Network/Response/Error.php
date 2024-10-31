<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Network\Response;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Response from some CURL requests contains an error trace.
 */
class Error extends Model
{
    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function __construct(
        public readonly string $traceId,
        public readonly string $code,
        public readonly string $message,
        public readonly string $timestamp,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateTraceId();
        $this->validateCode();
        $this->validateMessage();
        $this->validateTimestamp();
    }

    /**
     * @throws EmptyValueException
     */
    public function validateTraceId(): void
    {
        $this->stringValidation->notEmpty(value: $this->traceId);
    }

    /**
     * @throws EmptyValueException
     */
    public function validateCode(): void
    {
        $this->stringValidation->notEmpty(value: $this->code);
    }

    /**
     * @throws EmptyValueException
     */
    public function validateMessage(): void
    {
        $this->stringValidation->notEmpty(value: $this->message);
    }

    /**
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    public function validateTimestamp(): void
    {
        $this->stringValidation->notEmpty(value: $this->timestamp);
        $this->stringValidation->isTimestampDate(value: $this->timestamp);
    }
}
