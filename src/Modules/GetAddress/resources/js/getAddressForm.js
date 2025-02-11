// noinspection JSValidateJSDoc

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Automatically set customer type.
 */
var getAddressCustomerType;

/**
 * The getAddress widget.
 */
var getAddressWidget;

/**
 * Instant update of the customer type in the session using native trigger events, handled
 * by jQuery. By doing this, we simplify customer type customization, and furthermore, we may
 * no longer need the customertypehandler.js handler. This part is controlled by the widget.
 *
 * @param getAddressCustomerType
 * @todo Evaluate customertypehandler.js, see if it can be entirely replaced.
 */
jQuery(document).on('update_resurs_customer_type', function (ev, customerType) {
    // noinspection JSUnresolvedReference (rbCustomerTypeData are owned by other parts in the plugin)
    jQuery.ajax(
        {
            url: rbCustomerTypeData['apiUrl'] + '&customerType=' + customerType,
        }
    )
});
jQuery(document).ready(function () {
    if (typeof Resursbank_GetAddress !== 'function' ||
        document.getElementById('rb-ga-widget') === null
    ) {
        return;
    }

    getAddressWidget = new Resursbank_GetAddress(
        {
            updateAddress: function (data) {
                getAddressCustomerType = this.getCustomerType();
                rbHandleFetchAddressResponse(data, getAddressCustomerType);
            }
        }
    );
    try {
        getAddressWidget.setupEventListeners();
        var naturalEl = getAddressWidget.getCustomerTypeElNatural();
        var legalEl = getAddressWidget.getCustomerTypeElLegal();
        naturalEl.addEventListener('change', function () {
            jQuery('body').trigger('update_resurs_customer_type', ['NATURAL']);
        });
        legalEl.addEventListener('change', function () {
            jQuery('body').trigger('update_resurs_customer_type', ['LEGAL']);
        });
    } catch (e) {
        console.log(e);
    }
});

const rbHandleFetchAddressResponse = (() => {
    /**
     * @namespace Rb
     */

    /**
     * @namespace Rb.GetAddress
     */

    /**
     * @memberOf Rb.GetAddress
     * @name MappedAddressEl
     * @typedef {object}
     * @property {string} name
     * @property {HTMLInputElement} el
     */

    /**
     * @memberOf Rb.GetAddress
     * @name AddressFields
     * @typedef {object}
     * @property {Rb.GetAddress.MappedAddressEl[]} billing
     * @property {Rb.GetAddress.MappedAddressEl[]} shipping
     */

    /**
     * @memberOf Rb
     * @name Address
     * @typedef {object}
     * @property {string|null} addressRow1
     * @property {string|null} addressRow2
     * @property {string|null} countryCode
     * @property {string|null} firstName
     * @property {string|null} fullName
     * @property {string|null} lastName
     * @property {string|null} postalArea
     * @property {string|null} postalCode
     * @property {string|null} addressRow1
     */

    /**
     * @returns {HTMLFormElement|null}
     */
    const getCheckoutForm = () => {
        const form = document.forms['checkout'];

        return form instanceof HTMLFormElement ? form : null;
    };

    /**
     * A filter to get elements with the "name" attribute.
     *
     * @param {HTMLElement} el
     * @returns {boolean}
     */
    const getNamedFields = (el) => el.hasAttribute('name');

    /**
     * A filter to get elements whose `name` starts with `"billing"`.
     *
     * @param {HTMLInputElement} el
     * @returns {boolean}
     */
    const getBillingFields = (el) => el.name.startsWith('billing');

    /**
     * A filter to get elements whose `name` starts with `"shipping"`.
     *
     * @param {HTMLInputElement} el
     * @returns {boolean}
     */
    const getShippingFields = (el) => el.name.startsWith('shipping');

    /**
     * Maps an address field `name` to the Resurs Bank address model
     * equivalent. The mapped names are taken from the data of the returned
     * response when fetching a customer address.
     *
     * @param {string} name
     * @returns {string}
     */
    const mapResursFieldName = (name) => {
        let result;

        switch (name.split('billing_')[1] || name.split('shipping_')[1]) {
            case 'first_name':
                result = 'firstName';
                break;
            case 'last_name':
                result = 'lastName';
                break;
            case 'country':
                result = 'countryCode';
                break;
            case 'address_1':
                result = 'addressRow1';
                break;
            case 'address_2':
                result = 'addressRow2';
                break;
            case 'postcode':
                result = 'postalCode';
                break;
            case 'city':
                result = 'postalArea';
                break;
            case 'company':
                // Resurs Bank puts the company name in the key "fullName" in
                // the fetched address data.
                result = 'fullName';
                break;
            default:
                result = '';
        }

        return result;
    }

    /**
     * Maps an address field element to an object which includes the element
     * and its `name` which has been mapped to a Resurs Bank equivalent.
     *
     * @param {HTMLInputElement} el
     * @returns {Rb.GetAddress.MappedAddressEl}
     */
    const mapResursField = (el) => ({name: mapResursFieldName(el.name), el});

    /**
     * A filter to remove address fields that are not used by Resurs Bank.
     *
     * @param {Rb.GetAddress.MappedAddressEl} obj
     * @returns {boolean}
     */
    const getUsableFields = (obj) => obj.name !== '';

    /**
     * Maps an array of elements to an array of fields.
     *
     * @param {Element[]} els
     * @returns {Rb.GetAddress.MappedAddressEl[]}
     */
    const mapResursFields = (els) =>
        els.map(mapResursField).filter(getUsableFields);

    /**
     * Gathers and returns an object with both billing and shipping address
     * fields. Each section is a list with {@see Rb.GetAddress.MappedAddressEl}
     * values.
     *
     * @param {HTMLFormElement|null} form
     * @return {null|Rb.GetAddress.AddressFields}
     */
    const getAddressFields = (form) => {
        let result = null;
        if (form instanceof HTMLFormElement) {
            const arr = Array.from(form.elements);
            const namedFields = arr.filter(getNamedFields);

            result = {};
            result.billing = mapResursFields(
                namedFields.filter(getBillingFields)
            );
            result.shipping = mapResursFields(
                namedFields.filter(getShippingFields)
            );
        }

        return result;
    }

    /**
     * Updates checkout address fields with the supplied address data.
     *
     * @param {Rb.Address} data
     * @param {"NATURAL"|"LEGAL"} customerType
     */
    const updateAddressFields = (data, customerType) => {
        const fields = getAddressFields(getCheckoutForm());

        const billingResursGovId = jQuery('#billing_resurs_government_id');

        if (typeof getAddressWidget !== 'undefined' && customerType === 'LEGAL') {
            const govIdElement = getAddressWidget.getGovIdElement();
            if (billingResursGovId.length > 0) {
                billingResursGovId.val(govIdElement.value);
            }
        }

        fields?.billing.forEach((obj) => {
            const dataVal = data[obj.name];
            const newVal = typeof dataVal === 'string' ? dataVal : obj.el.value;
            // If-statement to avoid populating company field if customer type
            // is "NATURAL".
            if (obj.name === 'fullName') {
                if (customerType === "LEGAL") {
                    obj.el.value = newVal;
                } else {
                    obj.el.value = '';
                    billingResursGovId.val('');
                }
            } else {
                obj.el.value = newVal;
            }

            if (typeof obj.el.parentNode.parentNode.classList === 'object' &&
                obj.el.parentNode.parentNode.classList.contains('woocommerce-invalid')
            ) {
                obj.el.parentNode.parentNode.classList.remove('woocommerce-invalid');
                obj.el.parentNode.parentNode.classList.remove('woocommerce-invalid-required-field');
            }
        });
    };

    const billingResursGovId = jQuery('#billing_resurs_government_id');

    return (data, customerType) => {
        try {
            if (billingResursGovId.length > 0) {
                if (customerType === 'LEGAL') {
                    // Prefill company government id if getAddress was used and we have the billing
                    // fields available.
                    if (jQuery('#rb-customer-widget-getAddress-input-govId').length > 0) {
                        billingResursGovId.val(jQuery('#rb-customer-widget-getAddress-input-govId').val());
                    }
                } else {
                    // Empty value for LEGAL when switched.
                    billingResursGovId.val('');
                }
            }
            updateAddressFields(data, customerType);
            rbUpdateCustomerType();
        } catch (e) {
            console.log(e);
        }
    };
})();
