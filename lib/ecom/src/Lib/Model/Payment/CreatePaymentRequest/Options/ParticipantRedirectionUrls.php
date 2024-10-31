<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Options;

use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUrl;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Application data for a payment.
 */
class ParticipantRedirectionUrls extends Model
{
    public function __construct(
        #[StringIsUrl] public readonly ?string $failUrl,
        #[StringIsUrl] public readonly ?string $successUrl
    ) {
        parent::__construct();
    }
}
