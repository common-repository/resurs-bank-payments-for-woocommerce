<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Locale;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringNotEmpty;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Translated phrase. The phrase has to be translated to the languages listed
 * in the constructor, and cannot be an empty string. Base language is english.
 */
class Translation extends Model
{
    /** @var string Translation string for Swedish (sv_SE). */
    public string $sv;

    /** @var string Translation string for Finnish (fi_FI). */
    public string $fi;

    /** @var string Translation string for Norwegian (Variants no_NO, nb_NO, nn_NO). */
    public string $no;

    /** @var string Translation string for Danish (da_DK). */
    public string $da;

    /**
     * Translations for multiple languages, with failover to english.
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function __construct(
        #[StringNotEmpty] public readonly string $en,
        string $sv = '',
        string $fi = '',
        string $no = '',
        string $da = ''
    ) {
        if (trim(string: $sv) === '') {
            $sv = $en;
        }

        if (trim(string: $fi) === '') {
            $fi = $en;
        }

        if (trim(string: $no) === '') {
            $no = $en;
        }

        if (trim(string: $da) === '') {
            $da = $en;
        }

        $this->sv = $sv;
        $this->fi = $fi;
        $this->no = $no;
        $this->da = $da;

        parent::__construct();
    }
}
