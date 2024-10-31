<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Converter;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\OrderLineType;

/**
 * Collection container for DiscountItem objects.
 */
class DiscountItemCollection extends Collection
{
    /**
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: DiscountItem::class);
    }

    /**
     * Find collection item with matching rate property value.
     */
    public function getByRate(
        float $rate
    ): ?DiscountItem {
        $result = null;

        foreach ($this->getData() as $item) {
            if ($item->rate === $rate) {
                $result = $item;
                break;
            }
        }

        return $result;
    }

    /**
     * Add rate data (create rate if it does not exist and append amount).
     *
     * @throws IllegalTypeException|IllegalValueException
     */
    public function addRateData(
        float $rate,
        float $amount
    ): DiscountItem {
        $result = $this->getByRate(rate: $rate);

        if ($result === null) {
            $result = new DiscountItem(
                rate: round(num: $rate, precision: 2)
            );

            $this->offsetSet(offset: null, value: $result);
        }

        $result->amount = round(num: $result->amount + $amount, precision: 2);

        return $result;
    }

    /**
     * Return simple array of OrderLine instances based on the data contained in
     * this collection.
     *
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ConfigException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws IllegalValueException
     */
    public function getOrderLines(): OrderLineCollection
    {
        $result = [];

        foreach ($this->getData() as $discount) {
            $result[] = new OrderLine(
                quantity: 1,
                quantityUnit: Translator::translate(
                    phraseId: 'default-quantity-unit'
                ),
                vatRate: round(num: $discount->rate, precision: 2),
                totalAmountIncludingVat: round(
                    num: -$discount->amount,
                    precision: 2
                ),
                description: Translator::translate(
                    phraseId: 'discount'
                ),
                reference: "discount_$discount->rate",
                type: OrderLineType::DISCOUNT
            );
        }

        return new OrderLineCollection(data: $result);
    }
}
