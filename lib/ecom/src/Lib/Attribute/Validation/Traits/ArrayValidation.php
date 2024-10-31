<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Attribute\Validation\Traits;

use Exception;
use Resursbank\Ecom\Lib\Utilities\Random;
use Resursbank\Ecom\Lib\Utilities\Random\DataType;

/**
 * Shared array validation traits.
 */
trait ArrayValidation
{
    /**
     * @throws Exception
     */
    public function getRandom(
        int $min,
        int $max,
        ?DataType $type = null
    ): array {
        $result = [];
        $count = random_int(min: $min, max: $max);

        for ($i = 0; $i < $count; $i++) {
            $result[] = match ($type) {
                DataType::STRING => Random::getString(),
                default => Random::getTypeValue(
                    type: $type ?? Random::getType()
                )
            };
        }

        return $result;
    }

    /**
     * Append randomized values which will be rejected by property validation.
     *
     * Note: cannot generate rejected random values without min / max (no min
     * / max = all values are allowed).
     *
     * @throws Exception
     */
    private function addRandomRejectedValues(
        array &$result,
        int $size,
        int $min,
        ?int $max,
        ?DataType $type = null
    ): void {
        $count = 0;

        // Generate random values.
        while ($count < $size) {
            if ($max !== null) {
                $result[] = $this->getRandom(
                    min: $max + 1,
                    max: $max + 49,
                    type: $type
                );
                $count++;
            }

            if ($min <= 0) {
                continue;
            }

            $result[] = $this->getRandom(min: 0, max: $min - 1, type: $type);
            $count++;
        }
    }
}
