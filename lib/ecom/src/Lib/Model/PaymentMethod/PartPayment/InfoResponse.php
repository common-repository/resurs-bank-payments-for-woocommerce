<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod\PartPayment;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Response object for AJAX requests to part payment widget HTML updates.
 */
class InfoResponse extends Model
{
    public function __construct(
        public readonly float $startingAt,
        public readonly string $startingAtHtml,
        public readonly string $readMoreWidget
    ) {
        parent::__construct();
    }
}
