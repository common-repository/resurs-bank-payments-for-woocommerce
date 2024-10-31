/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

var variationDisplayPrice = 0;

/**
 * Get current price based on variation or single product prices.
 * @returns {*|number}
 */
function getRbPpPriceFromWooCom() {
    return (
        typeof rbPpScript !== 'undefined' &&
        typeof rbPpScript.product_price !== 'undefined' &&
        variationDisplayPrice === 0
    ) ? rbPpScript.product_price
        : variationDisplayPrice;
}

jQuery(document).ready(function () {
    const qtyElement = document.querySelector('input.qty');

    if (typeof Resursbank_PartPayment !== 'undefined' && null !== document.getElementById('rb-pp-widget-container')) {
        // noinspection JSUndeclaredVariable (Ecom owned)
        RB_PP_WIDGET_INSTANCE = Resursbank_PartPayment.createInstance(
            document.getElementById('rb-pp-widget-container'),
            {
                getAmount: function () {
                    // noinspection JSUnresolvedReference
                    return getRbPpPriceFromWooCom() * this.getQty();
                },
                getObservableElements: function () {
                    return [qtyElement];
                },
                getQtyElement: function () {
                    return qtyElement;
                }
            }
        );

        jQuery('.variations_form').each(function () {
            jQuery(this).on('found_variation', function (event, variation) {
                // noinspection JSUnresolvedReference (Woocommerce owned variables)
                variationDisplayPrice = variation.display_price;
                // noinspection JSIgnoredPromiseFromCall
                RB_PP_WIDGET_INSTANCE.updateStartingCost();
            });
        });
    }
});
