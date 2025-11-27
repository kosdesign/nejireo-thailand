/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'ko',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'mage/translate',
    'underscore'
], function (ko, Component, quote, totals, $t, _) {
    'use strict';

    var isFullTaxSummaryDisplayed = window.checkoutConfig.isFullTaxSummaryDisplayed;
    return Component.extend({
        defaults: {
            template: 'Kos_BssOneStepCheckout/summary/item/details/price/unitprice',
            displayArea: 'unitprice_incl_tax'
        },
        isFullTaxSummaryDisplayed: isFullTaxSummaryDisplayed,

        /**
         * @param {Object} item
         * @return {Number}
         */
        getUnitPrice: function (item) {
            if (this.ifShowTax()) {
                return parseFloat(item['price_incl_tax']);
            }
            return parseFloat(item['price']);
        },

        /**
         *
         * @returns {boolean}
         */
        ifShowTax:function () {
            if (isFullTaxSummaryDisplayed) {
                return isFullTaxSummaryDisplayed;
            }
            return false;
        }
    });
});

