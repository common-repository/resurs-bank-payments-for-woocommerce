<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Validation\ArrayValidation;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Enum\ActionType;

/**
 * Defines an action log item.
 */
class ActionLog extends Model
{
    /**
     * @throws IllegalValueException|EmptyValueException
     */
    public function __construct(
        public readonly string $actionId,
        public readonly ActionType $type,
        public readonly string $created,
        public readonly OrderLineCollection $orderLines,
        public readonly ?string $transactionId = null,
        public readonly ?string $creator = null,
        private readonly StringValidation $stringValidation = new StringValidation(),
        private readonly ArrayValidation $arrayValidation = new ArrayValidation()
    ) {
        $this->validateActionId();
        $this->validateCreated();
        $this->validateOrderLines();
    }

    /**
     * @throws IllegalValueException
     * @throws EmptyValueException
     */
    private function validateActionId(): void
    {
        $this->stringValidation->notEmpty(value: $this->actionId);
        $this->stringValidation->isUuid(value: $this->actionId);
    }

    /**
     * NOTE: We cannot test date format because Resurs Bank will return
     * inconsistent values for the same properties (sometimes ATOM compatible,
     * sometimes containing an up to 9 digit microsecond suffix).
     *
     * @throws IllegalValueException
     * @throws EmptyValueException
     */
    private function validateCreated(): void
    {
        $this->stringValidation->notEmpty(value: $this->created);
        $this->stringValidation->isTimestampDate(value: $this->created);
    }

    /**
     * @throws IllegalValueException
     */
    private function validateOrderLines(): void
    {
        $this->arrayValidation->isSequential(
            data: $this->orderLines->toArray()
        );
        $this->arrayValidation->length(
            data: $this->orderLines->toArray(),
            min: 1,
            max: 1000
        );
    }
}
