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
 * @category   BSS
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'Bss_QuoteExtension/js/view/sidebar',
    'mage/translate'
], function (Component, customerData, $, ko, _) {
    'use strict';

    var sidebarInitialized = false,
        addToQuoteCalls = 0,
        miniQuote;

    miniQuote = $("[data-block='miniquote']");

    /**
     * @return {Boolean}
     */
    function initSidebar() {
        if (miniQuote.data('quoteSidebar')) {
            miniQuote.quoteSidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniQuote.trigger('contentUpdated');

        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;
        miniQuote.quoteSidebar({
            'targetElement': 'div.minicart-dropdown',
            'url': {
                'update': window.quote.updateItemQtyUrl,
                'remove': window.quote.removeItemUrl,
                'loginUrl': window.quote.customerLoginUrl,
                'clear' : window.quote.clearQuote,
                'isRedirectRequired': window.quote.isRedirectRequired
            },
            'button': {
                'remove': '#mini-quote a.action.delete',
                'close': '#btn-miniquote-close',
                'clear' : '#miniquote-content-wrapper .action.clear.quote'
            },
            'showquote': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'miniquote': {
                'list': '#mini-quote',
                'content': '#miniquote-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.quote.miniQuoteMaxItemsVisible
            },
            'item': {
                'qty': ':input.quote-item-qty',
                'button': ':button.update-quote-item'
            },
            'confirmMessage': $.mage.__('Are you sure you would like to remove this item from the quote?'),
            'confirmRemoveAllMessage': $.mage.__('Are you sure you would like to remove all items from the quote?')
        });
        if(!$('#miniquote-content-wrapper .card-table-data table tbody #mini-quote').length){
            $('#miniquote-content-wrapper .card-table-data table tbody').append($('#mini-quote'));
        }

        $.each($('#miniquote-content-wrapper .card-table-data table tbody #mini-quote tr .col-price .price'), function( index, value ) {
            var product_price = $(this).parent().parent().parent().parent().children('input.input-price').val();
            if(product_price == ""){
                var price = $(this).html().substr(1);
                $(this).parent().parent().parent().parent().children('input.input-price').val(price);
            }
            var price = $(this).parent().parent().parent().parent().children('input.input-price').val();
            var pri = price.split(",");
            var _price = '';
            $.each( pri, function( key, value ) {
                _price += value;
            });

            var currency = $(this).html().substr(0, 1);
            var qty = $(this).parent().parent().parent().parent().parent().children('td.col.text-center.col-qty').children('span').html();
            var sum = parseFloat(_price) * parseFloat(qty);
            var toFixed = sum.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");//.toFixed(2);
            $(this).html(currency + toFixed);
        });
    }

    miniQuote.on('dropdowndialogopen', function () {
        initSidebar();
        if(!$('#miniquote-content-wrapper .card-table-data table tbody #mini-quote').length){
            $('#miniquote-content-wrapper .card-table-data table tbody').append($('#mini-quote'));
        }

        $.each($('#miniquote-content-wrapper .card-table-data table tbody #mini-quote tr .col-price .price'), function( index, value ) {
            var product_price = $(this).parent().parent().parent().parent().children('input.input-price').val();
            if(product_price == ""){
                var price = $(this).html().substr(1);
                $(this).parent().parent().parent().parent().children('input.input-price').val(price);
            }
            var price = $(this).parent().parent().parent().parent().children('input.input-price').val();
            var pri = price.split(",");
            var _price = '';
            $.each( pri, function( key, value ) {
                _price += value;
            });

            var currency = $(this).html().substr(0, 1);
            var qty = $(this).parent().parent().parent().parent().parent().children('td.col.text-center.col-qty').children('span').html();
            var sum = parseFloat(_price) * parseFloat(qty);
            var toFixed = sum.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");//.toFixed(2);
            $(this).html(currency + toFixed);
        });
    });

    return Component.extend({
        quoteExtensionUrl: window.quote.quoteExtensionUrl,
        clearQuote: window.quote.clearQuote,
        maxItemsToDisplay: window.quote.maxItemsToDisplay,
        quote: {},

        /**
         * @override
         */
        initialize: function () {
            var self = this,
                quoteData = customerData.get('quote');

            this.update(quoteData());
            quoteData.subscribe(function (updatedQuote) {
                addToQuoteCalls--;
                this.isLoading(addToQuoteCalls > 0);
                sidebarInitialized = false;
                this.update(updatedQuote);
                initSidebar();
            }, this);
            $('[data-block="miniquote"]').on('contentLoading', function () {
                addToQuoteCalls++;
                self.isLoading(true);
            });

            if (quoteData()['website_id'] !== window.quote.websiteId) {
                customerData.reload(['quote'], false);
            }

            return this._super();
        },
        isLoading: ko.observable(false),
        initSidebar: initSidebar,

        /**
         * Close mini quote.
         */
        closeMiniQuote: function () {
            $('[data-block="miniquote"]').find('[data-role="dropdownDialog"]').dropdownDialog('close');
        },

        /**
         * @return {Boolean}
         */
        closeSidebar: function () {
            var miniquote = $('[data-block="miniquote"]');

            miniquote.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                miniquote.find('[data-role="dropdownDialog"]').dropdownDialog('close');
            });

            return true;
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini quote content.
         *
         * @param {Object} updatedQuote
         * @returns void
         */
        update: function (updatedQuote) {
            _.each(updatedQuote, function (value, key) {
                if (!this.quote.hasOwnProperty(key)) {
                    this.quote[key] = ko.observable();
                }
                this.quote[key](value);
            }, this);
        },

        /**
         * Get quote param by name.
         * @param {String} name
         * @returns {*}
         */
        getQuoteParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.quote.hasOwnProperty(name)) {
                    this.quote[name] = ko.observable();
                }
            }
            return this.quote[name]();
        },

        /**
         * Returns array of quote items, limited by 'maxItemsToDisplay' setting
         * @returns []
         */
        getQuoteItems: function () {
            var items = this.getQuoteParam('items') || [];

            items = items.slice(parseInt(-this.maxItemsToDisplay, 10));

            return items;
        },

        /**
         * Returns count of quote line items
         * @returns {Number}
         */
        getQuoteLineItemsCount: function () {
            var items = this.getQuoteParam('items') || [];

            return parseInt(items.length, 10);
        }
    });
});
