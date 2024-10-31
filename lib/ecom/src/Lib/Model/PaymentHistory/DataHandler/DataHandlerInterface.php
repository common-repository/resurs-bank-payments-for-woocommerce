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
 * Contract to implement log data handler.
 */
interface DataHandlerInterface
{
    /**
     * Resolve list of all log entries associated with supplied payment id.
     */
    public function getList(string $paymentId, ?Event $event): ?EntryCollection;

    /**
     * Write Entry instance to permanent storage.
     */
    public function write(Entry $entry): void;

    /**
     * Confirm whether an event has executed.
     */
    public function hasExecuted(string $paymentId, Event $event): bool;
}
