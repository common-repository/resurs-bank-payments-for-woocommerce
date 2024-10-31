<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Interface;

/**
 * Defines payment method entity contract.
 */
interface PaymentMethod
{
    public function getId(): string;

    public function getName(): string;

    public function getMinLimit(): float;

    public function getMaxLimit(): float;

    public function getSortOrder(): int;

    public function enabledForB2b(): bool;

    public function enabledForB2c(): bool;

    public function getTypeValue(): string;

    public function isInternal(): bool;
}
