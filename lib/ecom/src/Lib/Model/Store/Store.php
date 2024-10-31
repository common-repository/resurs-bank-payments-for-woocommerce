<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Store;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\IntValue;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Store\Enum\Country;

/**
 * Defines a Store resource collected from the API.
 */
class Store extends Model
{
    /**
     * @param string $id API identifier
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringIsUuid] #[StringNotEmpty] public readonly string $id,
        #[IntValue(min:1)] public readonly int $nationalStoreId,
        public readonly Country $countryCode,
        #[StringNotEmpty] public readonly string $name,
        public readonly ?string $organizationNumber = null
    ) {
        parent::__construct();
    }
}
