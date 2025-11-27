/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_OneStepCheckout
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'Bss_OneStepCheckout/js/view/summary/item/details',
    'mage/translate',
    'ko',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Bss_OneStepCheckout/js/action/update-item',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote'
], function ($, Component, $t, ko, _, customerData, updateItemAction, priceUtils, quote) {
    'use strict';

    var quoteItemData = window.checkoutConfig.quoteItemData,
        isQuoteCheckoutPage = window.checkoutConfig.isQuoteCheckout;
    return Component.extend({
        defaults: {
            template: 'Kos_BssOneStepCheckout/summary/item/details'
        },
        quoteItemData: quoteItemData,
        isQuoteCheckoutPage: isQuoteCheckoutPage,

        /**
         *
         * @param quoteItem
         * @returns {*}
         */
        getPartNo: function(quoteItem) {
            if (this.getItem(quoteItem.item_id).part_number) {
                return this.getItem(quoteItem.item_id).part_number;
            }
            return null;
        },

        isQuoteCheckoutPage: function() {
            if (isQuoteCheckoutPage) {
                return isQuoteCheckoutPage;
            }
            return false;
        },

        /**
         *
         * @param quoteItem
         * @returns {*}
         */
        getProductName: function(quoteItem) {
            if (this.getItem(quoteItem.item_id)) {
                return this.getItem(quoteItem.item_id).name;
            }
            return null;
        },

        /**
         *
         * @param quoteItem
         * @returns {*}
         */
        getMaterial: function(quoteItem) {
            if (this.getItem(quoteItem.item_id).material) {
                return this.getItem(quoteItem.item_id).material;
            }
            return null;
        },

        /**
         *
         * @param quoteItem
         * @returns {*}
         */
        getPlating: function(quoteItem) {
            if (this.getItem(quoteItem.item_id).plating) {
                return this.getItem(quoteItem.item_id).plating;
            }
            return null;
        },

        /**
         *
         * @param quoteItem
         * @returns {*}
         */
        getDiameter: function(quoteItem) {
            if (this.getItem(quoteItem.item_id).diameter) {
                return this.getItem(quoteItem.item_id).diameter;
            }
            return null;
        },

        /**
         *
         * @param quoteItem
         * @returns {*}
         */
        getLength: function(quoteItem) {
            if (this.getItem(quoteItem.item_id).length) {
                return this.getItem(quoteItem.item_id).length;
            }
            return null;
        },

        /**
         *
         * @param quoteItem
         * @returns {*}
         */
        getUnitPrice: function(quoteItem) {
            if (this.getItem(quoteItem.item_id).price) {
                return this.getItem(quoteItem.item_id).price;
            }
            return null;
        },

        /**
         *
         * @param item_id
         * @returns {*}
         */
        getItem: function(item_id) {
            var itemElement = null;
            _.each(this.quoteItemData, function(element, index) {
                if(element.item_id == item_id) {
                    itemElement = element;
                }
            })
            return itemElement;
        }
    });
});