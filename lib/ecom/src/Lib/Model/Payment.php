<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsDatetime;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsUuid;
use Resursbank\Ecom\Lib\Model\Payment\ApplicationResponse;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Model\Payment\Order;
use Resursbank\Ecom\Lib\Model\Payment\Order\PossibleAction as PossibleActionModel;
use Resursbank\Ecom\Lib\Model\Payment\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Payment\RejectedReason;
use Resursbank\Ecom\Lib\Model\Payment\TaskRedirectionUrls;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Module\Payment\Enum\PossibleAction;
use Resursbank\Ecom\Module\Payment\Enum\RejectedReasonCategory;
use Resursbank\Ecom\Module\Payment\Enum\Status;

/**
 * Payment model used in the GET /payment call.
 */
class Payment extends Model
{
    /**
     * Payment data container that is also used by Search. When Search is active, some
     * returned fields are not guaranteed to be present; those fields are also nullable.
     * Application and countryCode is currently not showing in Search, so to make
     * Search compatible with the Payment model, we are temporary setting the missing fields
     * with empty defaults.
     *
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @todo Missing unit tests ECP-254
     */
    public function __construct(
        #[StringIsUuid] public readonly string $id,
        #[StringIsDatetime] public readonly string $created,
        #[StringIsUuid] public readonly string $storeId,
        public readonly Customer $customer,
        public readonly Status $status,
        public readonly ?RejectedReason $rejectedReason = null,
        public readonly array $paymentActions = [],
        public readonly ?PaymentMethod $paymentMethod = null,
        public readonly ?CountryCode $countryCode = null,
        public readonly ?Order $order = null,
        public readonly ?ApplicationResponse $application = null,
        public readonly ?Metadata $metadata = null,
        public readonly ?TaskRedirectionUrls $taskRedirectionUrls = null
    ) {
        parent::__construct();
    }

    /**
     * Checks if payment can be cancelled.
     */
    public function canCancel(): bool
    {
        return $this->canPerformAction(actionType: PossibleAction::CANCEL);
    }

    /**
     * Checks if payment can be partially cancelled.
     */
    public function canPartiallyCancel(): bool
    {
        return $this->canPerformAction(
            actionType: PossibleAction::PARTIAL_CANCEL
        );
    }

    /**
     * Checks if payment can be captured.
     */
    public function canCapture(): bool
    {
        return $this->canPerformAction(actionType: PossibleAction::CAPTURE);
    }

    /**
     * Checks if payment can be partially captured.
     */
    public function canPartiallyCapture(): bool
    {
        return $this->canPerformAction(
            actionType: PossibleAction::PARTIAL_CAPTURE
        );
    }

    /**
     * Checks if payment can be refunded.
     */
    public function canRefund(): bool
    {
        return $this->canPerformAction(actionType: PossibleAction::REFUND);
    }

    /**
     * Checks if payment can be partially refunded.
     */
    public function canPartiallyRefund(): bool
    {
        return $this->canPerformAction(
            actionType: PossibleAction::PARTIAL_REFUND
        );
    }

    /**
     * Alias for canRefund.
     */
    public function canCredit(): bool
    {
        return $this->canRefund();
    }

    /**
     * Returns true if payment is frozen.
     */
    public function isFrozen(): bool
    {
        return $this->status === Status::FROZEN;
    }

    /**
     * Returns true if payment is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === Status::REJECTED;
    }

    /**
     * Returns true if payment is denied.
     */
    public function isRejectionReasonCreditDenied(): bool
    {
        return $this->isRejectedReason(
            reason: RejectedReasonCategory::CREDIT_DENIED
        );
    }

    /**
     * Returns true if payment timed out.
     */
    public function isRejectionReasonTimeout(): bool
    {
        return $this->isRejectedReason(reason: RejectedReasonCategory::TIMEOUT);
    }

    /**
     * Returns true if payment has insufficient funds.
     */
    public function isRejectionReasonInsufficientFunds(): bool
    {
        return $this->isRejectedReason(
            reason: RejectedReasonCategory::INSUFFICIENT_FUNDS
        );
    }

    /**
     * Returns true if payment is canceled.
     */
    public function isRejectionReasonCanceled(): bool
    {
        return $this->isRejectedReason(
            reason: RejectedReasonCategory::CANCELED
        );
    }

    /**
     * Returns true if payment is denied.
     */
    public function isRejectionReasonTechnicalError(): bool
    {
        return $this->isRejectedReason(
            reason: RejectedReasonCategory::TECHNICAL_ERROR
        );
    }

    /**
     * Returns true if payment is aborted by customer.
     */
    public function isRejectionReasonAbortedByCustomer(): bool
    {
        return $this->isRejectedReason(
            reason: RejectedReasonCategory::ABORTED_BY_CUSTOMER
        );
    }

    /**
     * Whether payment is processable.
     */
    public function isProcessable(): bool
    {
        return match ($this->status) {
            Status::ACCEPTED, Status::TASK_REDIRECTION_REQUIRED => true,
            default => false
        };
    }

    /**
     * Whether payment is captured.
     */
    public function isCaptured(): bool
    {
        if (!$this->order) {
            return false;
        }

        return
            !$this->canCapture() &&
            !$this->canPartiallyCapture() &&
            $this->order->authorizedAmount === 0.0 &&
            $this->order->capturedAmount > 0.0 &&
            $this->order->capturedAmount !== $this->order->refundedAmount
        ;
    }

    /**
     * Whether payment is refunded.
     */
    public function isRefunded(): bool
    {
        if (!$this->order) {
            return false;
        }

        return
            $this->order->authorizedAmount === 0.0 &&
            $this->order->capturedAmount > 0.0 &&
            $this->order->capturedAmount === $this->order->refundedAmount
        ;
    }

    /**
     * Whether payment is cancelled.
     */
    public function isCancelled(): bool
    {
        if (!$this->order) {
            return false;
        }

        return
            $this->order->authorizedAmount === 0.0 &&
            $this->order->canceledAmount === $this->order->totalOrderAmount
        ;
    }

    /**
     * Checks if rejection reason is the supplied reason.
     */
    private function isRejectedReason(RejectedReasonCategory $reason): bool
    {
        return $this->rejectedReason !== null &&
            $this->rejectedReason->category === $reason;
    }

    /**
     * Check if specified PossibleAction can be performed on this Payment
     */
    private function canPerformAction(PossibleAction $actionType): bool
    {
        if (!$this->order) {
            return false;
        }

        /** @var PossibleActionModel $action */
        foreach ($this->order->possibleActions as $action) {
            if ($action->action === $actionType) {
                return true;
            }
        }

        return false;
    }
}
