<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\FloatValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Order\OrderLineType;

/**
 * Defines a product in an order.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class OrderLine extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        #[FloatValue(min: 0.01, scale: 2)] public readonly float $quantity,
        #[StringLength(max: 50)] public readonly string $quantityUnit,
        #[FloatValue(
            min: 0,
            max: 100,
            scale: 2
        )] public readonly float $vatRate,
        #[FloatValue(scale: 2)] public readonly float $totalAmountIncludingVat,
        #[StringLength(max: 100)] public readonly ?string $description = null,
        #[StringLength(max: 50)] public readonly ?string $reference = null,
        public readonly ?OrderLineType $type = null,
        #[FloatValue(
            scale: 2
        )] public readonly ?float $unitAmountIncludingVat = null,
        #[FloatValue(scale: 2)] public readonly ?float $totalVatAmount = null
    ) {
        parent::__construct();
    }
}
