<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\PaymentHistory;

/**
 * Available payment history events and their translation strings for the log.
 */
enum Event: string
{
    case CAPTURE_REQUESTED = 'event-capture-requested';
    case CAPTURED = 'event-captured';
    case PARTIALLY_CAPTURED = 'event-partially-captured';
    case REFUND_REQUESTED = 'event-refund-requested';
    case REFUNDED = 'event-refunded';
    case PARTIALLY_REFUNDED = 'event-partially-refunded';
    case CANCEL_REQUESTED = 'event-cancel-requested';
    case CANCELED = 'event-canceled';
    case PARTIALLY_CANCELLED = 'event-partially-cancelled';
    case REQUEST_FAILED = 'event-request-failed';
    case CALLBACK_AUTHORIZATION = 'event-callback-authorization';
    case CALLBACK_MANAGEMENT = 'event-callback-management';
    case CALLBACK_COMPLETED = 'event-callback-completed';
    case CALLBACK_FAILED = 'event-callback-failed';
    case REACHED_ORDER_SUCCESS_PAGE = 'event-reached-order-success-page';
    case REACHED_ORDER_FAILURE_PAGE = 'event-reached-order-failure-page';
    case ORDER_CANCELED = 'event-order-canceled';
    case ORDER_CANCELED_CRON = 'event-order-canceled-cron';
    case INVOICE_CREATED = 'event-invoice-created';
    case REDIRECTED_TO_GATEWAY = 'event-redirected-to-gateway';
    case LEGACY = 'event-legacy';
}
