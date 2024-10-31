<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PriceSignage;

use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUrl;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Defines URI link entity.
 */
class UriLink extends Model
{
    /**
     * @todo Can $language be empty? Is this an Enum value?
     */
    public function __construct(
        #[StringIsUrl] public readonly string $uri,
        public readonly string $language
    ) {
        parent::__construct();
    }
}
