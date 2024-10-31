<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringMatchesRegex;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * English phrase that can be translated into any language.
 */
class Phrase extends Model
{
    /**
     * @param string $id Regex match info from MAPI sometimes requires that the strings ends with dashes.
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringMatchesRegex(
            pattern: '/^[a-z0-9][a-z0-9\-]*[a-z0-9\-]$/'
        )] public string $id,
        public Translation $translation
    ) {
        parent::__construct();
    }
}
