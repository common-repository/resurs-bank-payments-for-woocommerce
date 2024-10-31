<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Address;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Lib\Model\Payment\Customer\DeviceInfo;
use Resursbank\Ecom\Lib\Order\CustomerType;
use Resursbank\Ecom\Lib\Validation\StringValidation;

use function is_string;

/**
 * Customer data supplied to create a payment.
 */
class Customer extends Model
{
    /**
     * NOTE: Regarding the $governmentId parameter definition. This data is not
     * required by Resurs Bank to successfully create a payment, if it isn't
     * supplied by us in the request to create the payment Resurs Bank will
     * request the client to enter it on the gateway. However, if we submit an
     * empty value, or NULL, this will cause the API to interpret the data which
     * results in an error. Thus, this value must be unset from this object
     * before we execute the request, and so it cannot be readonly. To summarize
     * nullable because it isn't required, not readonly since an empty string or
     * NULL causes a problem in the API request.
     *
     * @param string|null $governmentId To understand why this is nullable, and not readonly, see the note above.
     * @throws IllegalValueException
     * @todo There are no validation rules declared for anything. Like phone, email, government id etc.
     * @todo NOTE: This should technically be CustomerRequest, and there should be a customerResponse, see ECP-252
     * @todo Reason to avoid this is that governmentId validation will fail in CreatePayment CustomerResponse,
     * @todo Since it seems wrong in the API documentation we do not know what to do right now.
     */
    public function __construct(
        public readonly ?Address $deliveryAddress = null,
        public readonly ?CustomerType $customerType = null,
        public readonly ?string $contactPerson = null,
        public readonly ?string $email = null,
        public ?string $governmentId = null,
        public readonly ?string $mobilePhone = null,
        public readonly ?DeviceInfo $deviceInfo = null,
        protected readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validate();
    }

    /**
     * Validate object properties.
     *
     * NOTE: protected to allow override in CustomerResponse.
     *
     * @throws IllegalValueException
     */
    protected function validate(): void
    {
        $this->validateEmail();
    }

    /**
     * @throws IllegalValueException
     */
    private function validateEmail(): void
    {
        if (!is_string(value: $this->email)) {
            return;
        }

        $this->stringValidation->isEmail(value: $this->email);
    }
}
