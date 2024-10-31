<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\PaymentHistory;

use Resursbank\Ecom\Lib\Locale\Translator as Original;

/**
 * Utilize custom translation file.
 */
class Translator extends Original
{
    public static function translate(
        string $phraseId,
        ?string $translationFile = __DIR__ . '/translations.json'
    ): string {
        return parent::translate(
            phraseId: $phraseId,
            translationFile: $translationFile
        );
    }
}
