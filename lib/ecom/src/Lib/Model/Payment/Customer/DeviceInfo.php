<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model\Payment\Customer;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Lib\Attribute\Validation\StringIsIpAddress;
use Resursbank\Ecom\Lib\Attribute\Validation\StringLength;
use Resursbank\Ecom\Lib\Model\Model;

/**
 * Information and details about a payment.
 */
class DeviceInfo extends Model
{
    /**
     * @throws AttributeCombinationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function __construct(
        #[StringIsIpAddress] public readonly ?string $ip = null,
        #[StringLength(
            min: 1,
            max: 200
        )] public readonly ?string $userAgent = null
    ) {
        parent::__construct();
    }

    /**
     * Get and return a valid ip address.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getIp(): ?string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        if (!empty($ip) && filter_var(value: $ip, filter: FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return null;
    }

    /**
     * Get and return a valid User-Agent.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getUserAgent(): ?string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if (
            is_string(value: $userAgent) &&
            strlen(string: $userAgent) <= 200
        ) {
            return $userAgent;
        }

        return null;
    }
}
