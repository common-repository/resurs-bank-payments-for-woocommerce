<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Utilities\Random;

/**
 * Data-types we can randomize values for.
 */
enum DataType: string
{
    case STRING = 'STRING';
    case INT = 'INT';
    case FLOAT = 'FLOAT';
    case ARRAY = 'ARRAY';
    case BOOL = 'BOOL';
    case OBJECT = 'OBJECT';
}
