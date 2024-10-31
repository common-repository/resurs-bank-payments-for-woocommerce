<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentHistory;

use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Throwable;

/**
 * Repository of against payment history storage.
 */
class Repository
{
    /**
     * Connect to configured payment history storage and write info.
     *
     * @throws ConfigException
     */
    public static function write(
        Entry $entry
    ): void {
        Config::getPaymentHistoryDataHandler()->write(entry: $entry);
    }

    /**
     * Connect to configured payment history storage and get all log entries.
     *
     * @param string $paymentId Only returns entries with matching paymentId.
     * @param Event|null $event Only return entries with matching event.
     * @throws ConfigException
     */
    public static function getList(
        string $paymentId,
        ?Event $event = null
    ): ?EntryCollection {
        return Config::getPaymentHistoryDataHandler()->getList(
            paymentId: $paymentId,
            event: $event
        );
    }

    /**
     * Check whether an event has executed for given payment id.
     *
     * Connect to configured payment history storage, extract all entries with
     * matching paymentId and event and confirm whether there were any.
     *
     * @throws ConfigException
     * @noinspection PhpUnused
     */
    public static function hasExecuted(
        string $paymentId,
        Event $event
    ): bool {
        return Config::getPaymentHistoryDataHandler()->hasExecuted(
            paymentId: $paymentId,
            event: $event
        );
    }

    /**
     * Resolve formatted error message from Throwable.
     */
    public static function getError(
        Throwable $error
    ): string {
        $result = $error->getMessage() . "\n";
        $result .= $error->getFile() . ' :: ' . $error->getLine() . "\n";
        $result .= "--------------------------------------------------------------\n\n";
        $result .= $error->getTraceAsString();

        return $result;
    }
}
