<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler;

use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;

/**
 * Class to avoid payment history tracking.
 */
class VoidDataHandler implements DataHandlerInterface
{
    /**
     * @inheritDoc
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function write(Entry $entry): void
    {
        // Do nothing.
    }

    /**
     * @inheritDoc
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function getList(
        string $paymentId,
        ?Event $event = null
    ): ?EntryCollection {
        return null;
    }

    /**
     * @inheritDoc
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function hasExecuted(
        string $paymentId,
        Event $event
    ): bool {
        return true;
    }
}
