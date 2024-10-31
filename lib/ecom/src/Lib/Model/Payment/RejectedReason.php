<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment;

use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Model\Model;
use Resursbank\Ecom\Module\Payment\Enum\RejectedReasonCategory;
use Throwable;

/**
 * Model used to describe Payment rejection reason.
 */
class RejectedReason extends Model
{
    public function __construct(
        public readonly ?RejectedReasonCategory $category = null
    ) {
    }

    /**
     * Fetches a more human-readable description of the rejection reason.
     */
    public function getFriendlyDescription(): string
    {
        if ($this->category !== null) {
            try {
                return Translator::translate(
                    phraseId: str_replace(
                        search: '_',
                        replace: '-',
                        subject: strtolower(string: $this->category->value)
                    ),
                    translationFile: __DIR__ .
                    '/RejectedReason/Resources/translations.json'
                );
            } catch (Throwable) {
                return $this->category->value;
            }
        }

        return '';
    }
}
