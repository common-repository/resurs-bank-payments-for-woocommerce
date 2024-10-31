<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentMethod;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponse;
use Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponse\Type;
use Resursbank\Ecom\Lib\Model\PaymentMethod\ApplicationFormSpecResponse\ApplicationFormSpecElementResponseCollection;

use function array_filter;
use function in_array;

/**
 * Response object for application data specification calls
 */
class ApplicationFormSpecResponse extends Model
{
    public function __construct(
        public readonly ?ApplicationFormSpecElementResponseCollection $elements = null
    ) {
    }

    /**
     * Check if response contains a field with the specified name
     */
    public function hasField(string $fieldName): bool
    {
        if (!isset($this->elements)) {
            return false;
        }

        /** @var ApplicationFormSpecElementResponse $element */
        foreach ($this->elements as $element) {
            if ($element->fieldName === $fieldName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return a collection with only
     *
     * @throws IllegalTypeException
     */
    public function getFieldsByType(Type $type): ApplicationFormSpecElementResponseCollection
    {
        if (!isset($this->elements)) {
            return new ApplicationFormSpecElementResponseCollection(data: []);
        }

        $fields = array_filter(
            array: $this->elements->toArray(),
            callback: static fn (ApplicationFormSpecElementResponse $element) => $element->type === $type
        );

        return new ApplicationFormSpecElementResponseCollection(data: $fields);
    }

    /**
     * Filters out specified fields from field collection
     *
     * @return ApplicationFormSpecResponse
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function filter(string $property, array $fields): self
    {
        if (!isset($this->elements)) {
            // No point in filtering if we don't have a collection
            return $this;
        }

        $filtered = array_filter(
            array: $this->elements->toArray(),
            callback: static fn (ApplicationFormSpecElementResponse $element) => !in_array(
                needle: $element->{$property},
                haystack: $fields,
                strict: true
            )
        );

        return new self(
            elements: new ApplicationFormSpecElementResponseCollection(
                data: $filtered
            )
        );
    }
}
