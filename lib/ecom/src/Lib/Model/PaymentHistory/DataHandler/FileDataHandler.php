<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler;

use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Entry;
use Resursbank\Ecom\Lib\Model\PaymentHistory\EntryCollection;
use Resursbank\Ecom\Lib\Model\PaymentHistory\Event;
use Resursbank\Ecom\Lib\Utilities\DataConverter;

/**
 * Class to store and read payment history data from filesystem.
 */
class FileDataHandler implements DataHandlerInterface
{
    public function __construct(private readonly string $file)
    {
    }

    /**
     * @inheritDoc
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function write(Entry $entry): void
    {
        $currentCollection = $this->getList();
        $data = $currentCollection?->toArray() ?? [];
        $data[] = $entry;
        file_put_contents(
            filename: $this->file,
            data: json_encode(value: $data)
        );
    }

    /**
     * @inheritDoc
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function getList(
        ?string $paymentId = null,
        ?Event $event = null
    ): ?EntryCollection {
        $content = $this->getFileContent();

        $collection = !empty($content) ?
            DataConverter::arrayToCollection(
                data: $content,
                type: Entry::class
            ) : null;

        if ($collection !== null) {
            if (!$collection instanceof EntryCollection) {
                throw new IllegalTypeException(
                    message: 'The conversion did not result in an EntryCollection instance.'
                );
            }

            $collection = $this->filterCollection(
                collection: $collection,
                paymentId: $paymentId,
                event: $event
            );
        }

        return $collection;
    }

    /**
     * @inheritDoc
     * @throws IllegalTypeException
     * @throws ReflectionException
     * @throws IllegalValueException
     */
    public function hasExecuted(
        string $paymentId,
        Event $event
    ): bool {
        $collection = $this->getList(paymentId: $paymentId, event: $event);

        if ($collection === null) {
            return false;
        }

        /** @var Entry $entry */
        foreach ($collection as $entry) {
            if ($entry->event === $event) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter Entry data from array based on supplied paymentId and event.
     *
     * @throws IllegalTypeException
     */
    public function filterCollection(
        EntryCollection $collection,
        ?string $paymentId = null,
        ?Event $event = null
    ): EntryCollection {
        foreach ($collection as $key => $entry) {
            if (
                $entry instanceof Entry &&
                $this->isIdMatch(entry: $entry, paymentId: $paymentId) &&
                $this->isEventMatch(entry: $entry, event: $event)
            ) {
                continue;
            }

            $collection->offsetUnset(offset: $key);
        }

        /* Removing elements from the collection messes up its pointer. This can
           cause problems when iterating the collection, re-create to avoid. */
        return new EntryCollection(
            data: array_values(array: $collection->getData())
        );
    }

    /**
     * Whether paymentId matches the paymentId on the Entry instance.
     */
    public function isIdMatch(
        Entry $entry,
        ?string $paymentId
    ): bool {
        return $paymentId === null || $entry->paymentId === $paymentId;
    }

    /**
     * Whether event matches the event on the Entry instance.
     */
    public function isEventMatch(
        Entry $entry,
        ?Event $event
    ): bool {
        return $event === null || $entry->event === $event;
    }

    /**
     * Resolve file content as array of stdClass instances.
     */
    public function getFileContent(): array
    {
        if (!file_exists(filename: $this->file)) {
            return [];
        }

        $result = [];
        $content = file_get_contents(filename: $this->file);

        if ($content !== false) {
            $decodedData = json_decode(json: $content);

            if (
                is_array(value: $decodedData) &&
                json_last_error() === JSON_ERROR_NONE
            ) {
                $result = $decodedData;
            }
        }

        return $result;
    }
}
