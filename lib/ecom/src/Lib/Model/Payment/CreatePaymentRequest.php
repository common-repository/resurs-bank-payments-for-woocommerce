<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Application;
use Resursbank\Ecom\Lib\Model\Payment\CreatePaymentRequest\Options;

/**
 * Payment model used in a POST /payments request.
 */
class CreatePaymentRequest extends Model
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringIsUuid] public readonly string $storeId,
        #[StringIsUuid] public readonly string $paymentMethodId,
        public readonly Order $order,
        public readonly ?Application $application,
        public readonly ?Customer $customer,
        public readonly ?Metadata $metadata,
        public readonly ?Options $options
    ) {
        parent::__construct();
    }
}
