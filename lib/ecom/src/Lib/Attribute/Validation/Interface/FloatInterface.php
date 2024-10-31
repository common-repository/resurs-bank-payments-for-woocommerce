<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation\Interface;

/**
 * Contract for validation attributes attached to float properties.
 *
 * Please see the parent AttributeInterface for mor information.
 */
interface FloatInterface extends AttributeInterface
{
    /**
     * Confirm $value matches validation routine of property $name on Model
     */
    public function validate(string $name, float $value): void;
}
