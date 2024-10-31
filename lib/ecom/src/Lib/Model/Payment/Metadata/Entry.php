<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Metadata;

use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Single Metadata custom Entry
 */
class Entry extends Model
{
    public function __construct(
        #[StringLength(min: 1, max: 50)] public readonly string $key,
        #[StringLength(max: 1000)] public readonly string $value
    ) {
        parent::__construct();
    }
}
