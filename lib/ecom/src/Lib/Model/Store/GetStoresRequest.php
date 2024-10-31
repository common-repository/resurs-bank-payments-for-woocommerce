<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Store;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Validation\StringValidation;

/**
 * Request model to collect stores based on credentials. Useful for AJAX
 * requests to collect a list of available stores before credentials are
 * actually saved (enter credentials, reload list of stores, select store).
 */
class GetStoresRequest extends Model
{
    /**
     * @throws EmptyValueException
     */
    public function __construct(
        public readonly Environment $environment,
        public readonly string $clientId,
        public readonly string $clientSecret,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateClientId();
        $this->validateClientSecret();
    }

    /**
     * @throws EmptyValueException
     */
    private function validateClientId(): void
    {
        $this->stringValidation->notEmpty(value: $this->clientId);
    }

    /**
     * @throws EmptyValueException
     */
    private function validateClientSecret(): void
    {
        $this->stringValidation->notEmpty(value: $this->clientSecret);
    }
}
