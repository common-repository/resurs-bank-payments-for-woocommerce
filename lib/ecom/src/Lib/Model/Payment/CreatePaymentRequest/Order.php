<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\CollectionSize;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;

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
        #[CollectionSize(
            min:1,
            max: 1000
        )] public readonly OrderLineCollection $orderLines,
        #[StringMatchesRegex(pattern: '/^[\w\-_\/]{1,32}$/')]
        public readonly ?string $orderReference = null
    ) {
        parent::__construct();
    }
}
