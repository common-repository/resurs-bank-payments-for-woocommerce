<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Repository\Traits;

use InvalidArgumentException;
use ReflectionException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Collection\Collection;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Utilities\DataConverter;
use stdClass;

use function is_array;
use function is_string;

/**
 * Convert anonymous data to model instance(s).
 */
trait ModelConverter
{
    /**
     * Convert JSON data to model instance(s).
     *
     * @param class-string $model
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws ReflectionException
     */
    public function convertToModel(
        string|array|stdClass $data,
        string $model
    ): Collection|Model {
        $result = null;

        $this->validateModel(model: $model);

        if (is_string(value: $data)) {
            $data = json_decode(
                json: $data,
                associative: false,
                flags: JSON_THROW_ON_ERROR
            );
        }

        if (is_array(value: $data)) {
            $result = DataConverter::arrayToCollection(
                data: $data,
                type: $model
            );
        } elseif ($data instanceof stdClass) {
            $result = DataConverter::stdClassToType(
                object: $data,
                type: $model
            );
        }

        if (!$result instanceof Model && !$result instanceof Collection) {
            throw new IllegalValueException(message: 'Invalid data type');
        }

        return $result;
    }

    /**
     * @param class-string $model
     * @throws IllegalTypeException
     * @throws InvalidArgumentException
     */
    public function validateModel(
        string $model
    ): void {
        if (!class_exists(class: $model)) {
            throw new InvalidArgumentException(
                message: 'Model class does not exist.'
            );
        }

        if (!is_subclass_of(object_or_class: $model, class: Model::class)) {
            throw new IllegalTypeException(
                message: 'Model class is not a subclass of Model.'
            );
        }
    }
}
