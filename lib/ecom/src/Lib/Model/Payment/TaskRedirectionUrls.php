<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUrl;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Customer address data from a payment.
 */
class TaskRedirectionUrls extends Model
{
    public function __construct(
        #[StringIsUrl] public string $merchantUrl,
        #[StringIsUrl] public string $customerUrl,
        #[StringIsUrl] public ?string $coApplicantUrl = null
    ) {
    }
}
