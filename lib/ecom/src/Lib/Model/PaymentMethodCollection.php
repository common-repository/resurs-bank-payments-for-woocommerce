<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\MissingValueException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Model\Interface\PaymentMethodCollection as CollectionInterface;

/**
 * Defines a PaymentMethod collection.
 */
class PaymentMethodCollection extends Collection implements CollectionInterface
{
    /**
     * @throws IllegalTypeException
     */
    public function __construct(array $data)
    {
        parent::__construct(data: $data, type: PaymentMethod::class);
    }

    /**
     * @inheritDoc
     */
    public function getMethodName(string $methodId): string
    {
        $result = $methodId;

        /** @var PaymentMethod $method */
        foreach ($this->getData() as $method) {
            if ($method->getId() === $methodId) {
                $result = $method->getName();
                break;
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getById(string $methodId): PaymentMethod
    {
        /** @var PaymentMethod $method */
        foreach ($this->getData() as $method) {
            if ($method->getId() === $methodId) {
                return $method;
            }
        }

        throw new MissingValueException(
            message: 'Method with id ' . $methodId .
                ' does not exist in collection.'
        );
    }
}
