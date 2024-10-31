<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Callback;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Implementation of callback response object.
 */
class Response extends Model
{
    /**
     * @todo Consider validating httpCode if this can be done safely.
     */
    public function __construct(
        public readonly int $httpCode,
        public readonly string $note
    ) {
    }
}
