<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLogCollection;
use Resursbank\Ecom\Lib\Model\Payment\Order\PossibleActionCollection;

/**
 * Defines an order.
 */
class Order extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringMatchesRegex(
            pattern: '/^[\w\-_]{1,36}$/'
        )] public readonly string $orderReference,
        public readonly ActionLogCollection $actionLog,
        public readonly PossibleActionCollection $possibleActions,
        public readonly float $totalOrderAmount,
        public readonly float $canceledAmount,
        public readonly float $authorizedAmount,
        public readonly float $capturedAmount,
        public readonly float $refundedAmount
    ) {
        parent::__construct();
    }
}
