<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Options;

use Resursbank\Ecom\Lib\Model\Model;

/**
 * Application data for a payment.
 */
class RedirectionUrls extends Model
{
    public function __construct(
        public readonly ?ParticipantRedirectionUrls $customer,
        public readonly ?ParticipantRedirectionUrls $coApplicant,
        public readonly ?ParticipantRedirectionUrls $merchant
    ) {
    }
}
