<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Woocommerce\Modules\Order;

use JsonException;
use ReflectionException;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\PaymentMethod;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Woocommerce\Modules\PaymentInformation\PaymentInformation;
use Resursbank\Woocommerce\Util\Admin;
use Resursbank\Woocommerce\Util\Log;
use Resursbank\Woocommerce\Util\Metadata;
use Resursbank\Woocommerce\Util\Route;
use Resursbank\Woocommerce\Util\Translator;
use Resursbank\Woocommerce\Util\Url;
use Throwable;
use WC_Order;

/**
 * WC_Order related business logic.
 */
class Order
{
    /**
     * Initialize Order module.
     *
     * @noinspection PhpArgumentWithoutNamedIdentifierInspection
     */
    public static function init(): void
    {
        add_action(
            'add_meta_boxes',
            'Resursbank\Woocommerce\Modules\Order\Order::addPaymentInfo'
        );
        add_filter(
            'is_protected_meta',
            'Resursbank\Woocommerce\Modules\Order\Order::hideCustomFields',
            10,
            2
        );
    }

    /**
     * Add JavaScript to order view to update content when order is updated.
     */
    public static function initAdmin(): void
    {
        add_action(
            'admin_enqueue_scripts',
            'Resursbank\Woocommerce\Modules\Order\Order::initAdminScripts'
        );
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @noinspection PhpArgumentWithoutNamedIdentifierInspection
     */
    public static function initAdminScripts(): void
    {
        try {
            // Fetching the order id this way has historically been the best way on
            // sites where the normal way of doing it not works ("ecompress"). This however fails
            // when in HPOS-mode. If the solution below does not work, then we have to
            // reconsider the way this has been historically done,
            // $orderId = $_REQUEST['post'] ?? $_REQUEST['post_ID'] ?? $_REQUEST['order_id'] ?? null;
            $wcOrder = wc_get_order();

            if (
                !$wcOrder instanceof WC_Order ||
                !Metadata::isValidResursPayment(order: $wcOrder)
            ) {
                return;
            }

            $wcOrderid = $wcOrder->get_id();
            $fetchUrl = Route::getUrl(
                route: Route::ROUTE_ADMIN_GET_ORDER_CONTENT,
                admin: true
            );

            // Append JS code to observe order changes and fetch new content.
            $url = Url::getScriptUrl(
                module: 'Order',
                file: 'admin/getOrderContent.js'
            );

            wp_enqueue_script(
                'rb-get-order-content-admin-scripts',
                $url,
                ['jquery']
            );

            // Echo constant containing URL to get new order view content.
            wp_register_script(
                'rb-get-order-content-admin-inline-scripts',
                '',
                ['rb-get-order-content-admin-scripts']
            );
            wp_enqueue_script('rb-get-order-content-admin-inline-scripts');
            wp_add_inline_script(
                'rb-get-order-content-admin-inline-scripts',
                "RESURSBANK_GET_ORDER_CONTENT('$fetchUrl', '$wcOrderid');"
            );
        } catch (Throwable $error) {
            Log::error(error: $error);
        }
    }

    /**
     * Add action which will render payment information on order view.
     *
     * @noinspection PhpArgumentWithoutNamedIdentifierInspection
     */
    public static function addPaymentInfo(): void
    {
        try {
            $order = wc_get_order();
        } catch (Throwable) {
        }

        if (
            !($order instanceof WC_Order) ||
            !Metadata::isValidResursPayment(order: $order)
        ) {
            return;
        }

        add_meta_box(
            'resursbank_payment_info',
            'Resurs',
            'Resursbank\Woocommerce\Modules\Order\Order::renderPaymentInfo'
        );
    }

    /**
     * Render payment information box on order view.
     */
    public static function renderPaymentInfo(): void
    {
        $order = self::getCurrentOrder();

        try {
            if ($order === null || !Admin::isInShopOrderEdit()) {
                return;
            }

            $paymentInformation = new PaymentInformation(
                paymentId: Metadata::getPaymentId(order: $order)
            );

            $data = $paymentInformation->widget->content;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();

            $httpCode = $e->httpCode ?? 0;

            // According to APIs (when we get the codes), code 403 means the payment is either denied due to
            // the credentials or no longer available due to expiration.
            if ($httpCode === 403) {
                $errorMessage = Translator::translate(
                    phraseId: 'payment-info-denied-or-no-longer-available'
                );
            }

            $data = '<b>' .
                Translator::translate(
                    phraseId: 'failed-to-fetch-payment-data-from-the-server'
                ) . ' ' .
                Translator::translate(
                    phraseId: 'reason'
                ) . ':</b> ' . $errorMessage;

            Log::error(error: $e);
        }

        // @todo This feature most definitely needs sanitizing but since it is fetched from Ecom we
        // @todo need to figure out how the allowed tags should be handled.
        echo $data;
    }

    /**
     * Hide the plugin's custom fields from view.
     *
     * @SuppressWarnings(PHPMD.CamelCaseParameterName)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function hideCustomFields(mixed $protected, mixed $meta_key): mixed
    {
        if (
            str_starts_with(
                haystack: $meta_key,
                needle: RESURSBANK_MODULE_PREFIX . '_'
            )
        ) {
            return true;
        }

        return $protected;
    }

    /**
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     */
    public static function getPaymentMethod(
        WC_Order $order
    ): ?PaymentMethod {
        $method = (string)$order->get_payment_method();

        if ($method === '') {
            return null;
        }

        return Repository::getById(paymentMethodId: $method);
    }

    /**
     * Get currently viewed WP_Post as WP_Order instance, if any. For example,
     * while on the order view in admin we can obtain the currently viewed order
     * this way.
     */
    public static function getCurrentOrder(): ?WC_Order
    {
        try {
            $currentOrder = wc_get_order();

            if ($currentOrder instanceof WC_Order) {
                return $currentOrder;
            }
        } catch (Throwable) {
        }

        return null;
    }
}
