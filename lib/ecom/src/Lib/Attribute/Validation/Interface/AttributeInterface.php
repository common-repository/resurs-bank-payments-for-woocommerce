<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation\Interface;

use ReflectionParameter;

/**
 * Contract for validation attributes.
 *
 * The ReflectionParameter is the property being tested against, and can be
 * utilised for complex validation routines (for example, if the parameter
 * utilise both ArrayLength and ArrayTypeString validation attributes, the
 * getAcceptedValue/getRejectedValue methods MUST return an array matching both
 * validation routines).
 */
interface AttributeInterface
{
    /**
     * Resolves dataset with randomized values that will be accepted by the
     * validate() method on the Model class implementing the interface.
     *
     * This dataset is meant for testing the integrity of the validation
     * performed by the validate() method on the Model class.
     *
     * @param ReflectionParameter $parameter See class docblock for info.
     */
    public function getAcceptedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array;

    /**
     * Resolves dataset with randomized values that will be rejected by the
     * validate() method on the Model class implementing the interface.
     *
     * This dataset is meant for testing the integrity of the validation
     * performed by the validate() method on the Model class.
     *
     * @param ReflectionParameter $parameter See class docblock for info.
     */
    public function getRejectedValues(
        ReflectionParameter $parameter,
        int $size = 5
    ): array;
}
