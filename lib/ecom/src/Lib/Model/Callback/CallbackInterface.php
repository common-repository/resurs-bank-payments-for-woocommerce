<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

/**
 * Callback model contract.
 */
interface CallbackInterface
{
    /**
     * Resolve payment id related to callback.
     */
    public function getPaymentId(): string;

    /**
     * Resolve checkout id related to callback.
     */
    public function getCheckoutId(): ?string;

    /**
     * Resolve note (such as an order comment entry).
     */
    public function getNote(): string;
}
