<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

/**
 * @codingStandardsIgnoreStart
 */
enum Language: string
{
    /** en_EN */
    case EN = 'en';

    /** se_SV */
    case SV = 'sv';

    /**
     * Remember on integrations where Norwegian locales are not defined as NO.
     * Norwegian may have multiple definitions (e.g., nb-Norwegian Bokmål, nn-Nynorsk).
     * no_NO / nb_NO / nn_NO
     */
    case NO = 'no';

    /** fi_FI */
    case FI = 'fi';

    /** dk_DA */
    case DA = 'da';
}
