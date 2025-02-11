<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Order\PaymentMethod;

/**
 * Possible payment method types.
 *
 * @codingStandardsIgnoreStart
 */
enum Type: string
{
    case RESURS_CARD = 'RESURS_CARD';
    case RESURS_NEW_CARD = 'RESURS_NEW_CARD';
    case RESURS_REVOLVING_CREDIT = 'RESURS_REVOLVING_CREDIT';
    case RESURS_NEW_REVOLVING_CREDIT = 'RESURS_NEW_REVOLVING_CREDIT';
    case RESURS_INVOICE = 'RESURS_INVOICE';
    case RESURS_PART_PAYMENT = 'RESURS_PART_PAYMENT';
    case CARD = 'CARD';
    case DEBIT_CARD = 'DEBIT_CARD';
    case CREDIT_CARD = 'CREDIT_CARD';
    case SWISH = 'SWISH';
    case INTERNET = 'INTERNET';
    case PAYPAL = 'PAYPAL';
    case MASTERPASS = 'MASTERPASS';
    case OTHER = 'OTHER';
    case RESURS_ZERO = 'RESURS_ZERO';
    case RESURS_INVOICE_ACCOUNT = 'RESURS_INVOICE_ACCOUNT';
}
