/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Table_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
/**
 * Copyright � 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data'
], function($, ko, Component, selectShippingAddressAction, quote, formPopUpState, checkoutData, customerData) {
    'use strict';
    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Magetop_SellerTableRateShipping/shipping-address/address-renderer/default'
        },

        initObservable: function () {
            this._super();
            this.isSelected = ko.computed(function() {
                var isSelected = false;
                var shippingAddress = quote.shippingAddress();
                if (shippingAddress) {
                    isSelected = shippingAddress.getKey() == this.address().getKey();
                }
                return isSelected;
            }, this);

            return this;
        },

        getCountryName: function(countryId) {
            return (countryData()[countryId] != undefined) ? countryData()[countryId].name : "";
        },

        /** Set selected customer shipping address  */
        selectAddress: function() {
            selectShippingAddressAction(this.address());
            checkoutData.setSelectedShippingAddress(this.address().getKey());
        },

        editAddress: function() {
            formPopUpState.isVisible(true);
            this.showPopup();

        },
        showPopup: function() {
            $('[data-open-modal="opc-new-shipping-address"]').trigger('click');
        }
    });
});
