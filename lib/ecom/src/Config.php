<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom;

use Exception;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Log\LoggerInterface;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Config\Network;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\DataHandlerInterface;
use Resursbank\Ecom\Lib\Model\PaymentHistory\DataHandler\VoidDataHandler;
use Resursbank\Ecom\Module\PaymentMethod\Enum\CurrencyFormat;

use function dirname;

/**
 * API communication object.
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @noinspection PhpClassHasTooManyDeclaredMembersInspection
 */
final class Config
{
    /**
     * NOTE: This is a singleton class. Use Config::setup() to generate an
     * instance, use getter methods to extract properties safely.
     *
     * NOTE: Nullable to allow unsetting configuration.
     */
    private static ?Config $instance = null;

    /**
     * NOTE: By default we only log INFO level messages.
     *
     * @todo Create a null cache driver, so there always is one, returns null always
     * @todo Create a null database driver, so there always is one, returns null always
     */
    public function __construct(
        public readonly LoggerInterface $logger,
        public readonly CacheInterface $cache,
        public readonly ?Jwt $jwtAuth,
        public readonly DataHandlerInterface $paymentHistoryDataHandler,
        public readonly LogLevel $logLevel,
        public readonly bool $isProduction,
        public readonly Language $language,
        public readonly string $currencySymbol,
        public readonly CurrencyFormat $currencyFormat,
        public readonly Network $network,
        public readonly ?string $storeId = null
    ) {
    }

    /**
     * @noinspection PhpTooManyParametersInspection
     * @todo Consider making userAgent an object instead.
     * @todo Consider moving proxy, proxyType and timeout to a separate object.
     */
    public static function setup(
        LoggerInterface $logger = new NoneLogger(),
        CacheInterface $cache = new None(),
        ?Jwt $jwtAuth = null,
        DataHandlerInterface $paymentHistoryDataHandler = new VoidDataHandler(),
        LogLevel $logLevel = LogLevel::INFO,
        bool $isProduction = false,
        Language $language = Language::EN,
        string $currencySymbol = 'kr',
        CurrencyFormat $currencyFormat = CurrencyFormat::SYMBOL_LAST,
        Network $network = new Network(),
        ?string $storeId = null
    ): void {
        self::$instance = new Config(
            logger: $logger,
            cache: $cache,
            jwtAuth: $jwtAuth,
            paymentHistoryDataHandler: $paymentHistoryDataHandler,
            logLevel: $logLevel,
            isProduction: $isProduction,
            language: $language,
            currencySymbol: $currencySymbol,
            currencyFormat: $currencyFormat,
            network: $network,
            storeId: $storeId
        );
    }

    /**
     * Checks if JWT auth is configured
     */
    public static function hasJwtAuth(): bool
    {
        return isset(self::$instance->jwtAuth);
    }

    /**
     * Checks if there is a Config instance
     */
    public static function hasInstance(): bool
    {
        return isset(self::$instance);
    }

    /**
     * Clears active configuration
     */
    public static function unsetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * @throws ConfigException
     */
    public static function validateInstance(): void
    {
        if (self::$instance === null) {
            throw new ConfigException(
                message: 'Config instance not set. Please run Config::setup()'
            );
        }
    }

    /**
     * @throws ConfigException
     */
    public static function getLogger(): LoggerInterface
    {
        self::validateInstance();
        return self::$instance->logger;
    }

    /**
     * @throws ConfigException
     */
    public static function getPaymentHistoryDataHandler(): DataHandlerInterface
    {
        self::validateInstance();
        return self::$instance->paymentHistoryDataHandler;
    }

    /**
     * @throws ConfigException
     */
    public static function getCache(): CacheInterface
    {
        self::validateInstance();
        return self::$instance->cache;
    }

    /**
     * @throws ConfigException
     */
    public static function getJwtAuth(): ?Jwt
    {
        self::validateInstance();
        return self::$instance->jwtAuth;
    }

    /**
     * @throws ConfigException
     */
    public static function getLogLevel(): LogLevel
    {
        self::validateInstance();
        return self::$instance->logLevel;
    }

    /**
     * @throws ConfigException
     */
    public static function getUserAgent(): string
    {
        self::validateInstance();
        return self::$instance->network->userAgent;
    }

    /**
     * @throws ConfigException
     */
    public static function isProduction(): bool
    {
        self::validateInstance();
        return self::$instance->isProduction;
    }

    /**
     * @throws ConfigException
     */
    public static function getProxy(): string
    {
        self::validateInstance();
        return self::$instance->network->proxy;
    }

    /**
     * @throws ConfigException
     */
    public static function getProxyType(): int
    {
        self::validateInstance();
        return self::$instance->network->proxyType;
    }

    /**
     * @throws ConfigException
     */
    public static function getTimeout(): int
    {
        self::validateInstance();
        return self::$instance->network->timeout;
    }

    /**
     * @throws ConfigException
     */
    public static function getLanguage(): Language
    {
        self::validateInstance();
        return self::$instance->language;
    }

    /**
     * @throws ConfigException
     */
    public static function getCurrencySymbol(): string
    {
        self::validateInstance();
        return self::$instance->currencySymbol;
    }

    /**
     * @throws ConfigException
     */
    public static function getCurrencyFormat(): CurrencyFormat
    {
        self::validateInstance();
        return self::$instance->currencyFormat;
    }

    /**
     * @throws ConfigException
     */
    public static function getStoreId(): ?string
    {
        self::validateInstance();
        return self::$instance->storeId;
    }

    /**
     * Resolve path starting from the ECom root directory.
     *
     * @throws Exception If the path contains invalid traversal.
     */
    public static function getPath(string $dir = ''): string
    {
        $ecomRoot = dirname(path: __DIR__);

        // Prevent directory traversal by checking for '..'
        if (str_contains(haystack: $dir, needle: '..')) {
            throw new Exception(
                message: 'Invalid directory path. Directory traversal is not allowed.'
            );
        }

        // Resolve the final path relative to the ECom root
        return $ecomRoot . ($dir !== '' ? '/' . ltrim(
            string: $dir,
            characters: '/'
        ) : '');
    }
}
