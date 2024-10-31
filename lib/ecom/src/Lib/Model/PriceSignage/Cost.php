<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PriceSignage;

use JsonException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\FloatValue;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines cost entity.
 */
class Cost extends Model
{
    /**
     * @throws JsonException
     * @throws \ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[FloatValue(min: 0.0)] public readonly float $interest,
        #[IntValue(min: 0)] public readonly int $durationMonths,
        #[FloatValue(min: 0.0)] public readonly float $totalCost,
        #[FloatValue(min: 0.0)] public readonly float $monthlyCost,
        #[FloatValue(min: 0.0)] public readonly float $administrationFee,
        #[FloatValue(min: 0.0)] public readonly float $effectiveInterest
    ) {
        parent::__construct();
    }
}
