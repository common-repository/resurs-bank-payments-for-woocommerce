<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Module\Callback\Widget;

use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Widget\Widget;

/**
 * Callback URL list widget.
 */
class Callback extends Widget
{
    /** @var string */
    public readonly string $content;

    /** @var string */
    public readonly string $css;

    public function __construct(
        private readonly ?string $authorizationUrl = null,
        private readonly ?string $managementUrl = null
    ) {
        $this->renderWidget();
    }

    /**
     * Load widget stylesheet.
     *
     * @throws EmptyValueException
     */
    public static function getCss(): string
    {
        $css = file_get_contents(filename: __DIR__ . '/callback.css');

        if (!$css) {
            throw new EmptyValueException(
                message: 'Failed to load stylesheet.'
            );
        }

        return $css;
    }

    public function getAuthorizationUrl(): ?string
    {
        return $this->authorizationUrl ??
            Translator::translate(phraseId: 'failed-to-resolve-callback-url');
    }

    public function getManagementUrl(): ?string
    {
        return $this->managementUrl ??
            Translator::translate(phraseId: 'failed-to-resolve-callback-url');
    }

    /**
     * Render widget content.
     *
     * @throws FilesystemException
     * @throws EmptyValueException
     */
    protected function renderWidget(): void
    {
        /* @phpstan-ignore-next-line */
        $this->content = $this->render(file: __DIR__ . '/callback.phtml');

        /* @phpstan-ignore-next-line */
        $this->css = self::getCss();
    }
}
