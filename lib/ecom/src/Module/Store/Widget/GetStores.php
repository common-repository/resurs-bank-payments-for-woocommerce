<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Store\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Generate JavaScript code to fetch list of stores and update select element.
 */
class GetStores extends Widget
{
    /** @var string */
    public readonly string $content;

    /**
     * @param bool $automatic Automatically search on field change
     * @param string|null $storeSelectId ID of element to populate with new stores.
     * @param string|null $environmentSelectId ID of element containing environments.
     * @param string|null $clientIdInputId ID of client id input element.
     * @param string|null $clientSecretInputId ID of client secret element.
     * @param string|null $spinnerClass Class applied on store select element when fetching.
     * @throws FilesystemException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly bool $automatic = false,
        public readonly ?string $storeSelectId = null,
        public readonly ?string $environmentSelectId = null,
        public readonly ?string $clientIdInputId = null,
        public readonly ?string $clientSecretInputId = null,
        public readonly ?string $spinnerClass = null
    ) {
        $this->content = $this->render(file: __DIR__ . '/get-stores.js.phtml');
    }
}
