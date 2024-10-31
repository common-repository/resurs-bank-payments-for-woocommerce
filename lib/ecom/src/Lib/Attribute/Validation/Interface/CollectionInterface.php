<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation\Interface;

use Resursbank\Ecom\Lib\Collection\Collection;

/**
 * Contract for validation attributes attached to Collection properties.
 *
 * Please see the parent AttributeInterface for mor information.
 */
interface CollectionInterface extends AttributeInterface
{
    /**
     * Confirm $value matches validation routine of property $name on Model
     */
    public function validate(string $name, Collection $value): void;
}
