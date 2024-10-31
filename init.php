<?php

/**
 * Plugin Name: Resurs Bank Payments for WooCommerce
 * Description: Connect Resurs Bank as WooCommerce payment gateway.
 * WC Tested up to: 9.2.3
 * WC requires at least: 7.6.0
 * Plugin requires ecom: master
 * Requires PHP: 8.1
 * Version: 1.0.53
 * Author: Resurs Bank AB
 * Author URI: https://developers.resurs.com/
 * Plugin URI: https://developers.resurs.com/platform-plugins/woocommerce/resurs-merchant-api-2.0-for-woocommerce/
 * Text Domain: resurs-bank-payments-for-woocommerce
 * Requires Plugins: woocommerce
 *
 * @noinspection PhpCSValidationInspection
 * @noinspection PhpDefineCanBeReplacedWithConstInspection
 */

// @todo This is wordpress. SideEffects can not be handled properly while the init looks like this.
// @todo Consider honoring this in the future another way.
// phpcs:disable PSR1.Files.SideEffects


declare(strict_types=1);

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Resursbank\Ecom\Config;
use Resursbank\Woocommerce\Modules\Api\Connection;
use Resursbank\Woocommerce\Modules\ModuleInit\Admin as AdminInit;
use Resursbank\Woocommerce\Modules\ModuleInit\Frontend;
use Resursbank\Woocommerce\Modules\ModuleInit\Shared;
use Resursbank\Woocommerce\Util\Admin;
use Resursbank\Woocommerce\Util\WooCommerce;

if (!defined(constant_name: 'ABSPATH')) {
    exit;
}

define(
    constant_name: 'RESURSBANK_MODULE_DIR_NAME',
    value: substr(
        string: __DIR__,
        offset: strrpos(haystack: __DIR__, needle: '/') + 1
    )
);
define(constant_name: 'ALLOW_GET_ADDRESS', value: true);

require_once __DIR__ . '/autoload.php';

// Using same path identifier as the rest of the plugin-verse.
define(
    constant_name: 'RESURSBANK_GATEWAY_PATH',
    value: plugin_dir_path(file: __FILE__)
);
define(constant_name: 'RESURSBANK_MODULE_PREFIX', value: 'resursbank');

// Do not touch this just yet. Converting filters to something else than snake_cases has to be done
// in one sweep - if necessary.
define(constant_name: 'RESURSBANK_SNAKE_CASE_FILTERS', value: true);

// Translation domain is used for all phrases that is not relying on ecom2.
load_plugin_textdomain(
    domain: 'resurs-bank-payments-for-woocommerce',
    plugin_rel_path: dirname(
        path: plugin_basename(file: __FILE__)
    ) . '/language/'
);

// Make sure there is an instance of WooCommerce among active plugins.
if (!WooCommerce::isAvailable()) {
    return;
}

// Early initiation. If this request catches an exception, it is mainly caused by unset credentials.
Connection::setup();

// Cannot continue without ECom library instance configured.
if (!Config::hasInstance()) {
    return;
}

// Setup event listeners and resources when WP has finished loading all modules.
add_action(hook_name: 'plugins_loaded', callback: static function (): void {
    Shared::init();
    /** @noinspection PhpArgumentWithoutNamedIdentifierInspection */
    add_action(
        'before_woocommerce_init',
        static function (): void {
            if (!class_exists(class: FeaturesUtil::class)) {
                return;
            }

            FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    );

    if (Admin::isAdmin()) {
        AdminInit::init();
    } else {
        Frontend::init();
    }
});
