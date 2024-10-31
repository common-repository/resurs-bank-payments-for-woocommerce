<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentMethod\Http\PartPayment;

use Resursbank\Ecom\Lib\Model\PaymentMethod\PartPayment\InfoResponse;

/**
 * Contract to ensure stable integration of controller to fetch part payment
 * widget updates using AJAX.
 */
interface InfoControllerInterface
{
    public function getResponse(float $amount): InfoResponse;
}
