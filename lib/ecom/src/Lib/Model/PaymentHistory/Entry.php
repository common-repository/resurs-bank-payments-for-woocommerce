<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentHistory;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Payment history log entry data model.
 */
class Entry extends Model
{
    /**
     * Avoid property promotion to assign default value in body.
     */
    public readonly int $time;

    /**
     * @param string $paymentId Payment or Checkout ID
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @todo Consider ensuring $extra is JSON encoded. Not sure if this is desirable but seems sensible.
     */
    public function __construct(
        #[StringIsUuid] public readonly string $paymentId,
        public readonly Event $event,
        public readonly User $user,
        ?int $time = null,
        public readonly Result $result = Result::INFO,
        public readonly ?string $extra = null,
        public readonly ?string $previousOrderStatus = null,
        public readonly ?string $currentOrderStatus = null,
        public readonly ?string $reference = null,
        public readonly ?string $userReference = null
    ) {
        $this->time = $time ?? time();

        parent::__construct();
    }
}
