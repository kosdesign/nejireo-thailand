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
    'jquery',
    'mage/translate',
    'jquery/ui',
    'mage/validation'
], function($, $t) {
    "use strict";

    $.widget('bss.addToQuote', {

        options: {
            processStart: null,
            processStop: null,
            bindSubmit: true,
            miniquoteSelector: '[data-block="miniquote"]',
            messagesSelector: '[data-placeholder="messages"]',
            productStatusSelector: '.stock.available',
            addToQuoteButtonSelector: '.action.toquote',
            addToQuoteButtonDisabledClass: 'disabled',
            addToQuoteButtonTextWhileAdding: '',
            addToQuoteButtonTextAdded: '',
            addToQuoteButtonTextDefault: '',
            quoteFormUrl: null
        },

        _create: function() {
            $(this.element).parents('form').find('.quote-category').each(function() {
                $(this).remove();
            })
            if (this.options.bindSubmit) {
                this._bindSubmit();
            }
        },

        _bindSubmit: function () {
            var self = this;
            var dataValidate = false;
            this.element.on('click', function (e) {
                var form = $(this).closest("form"),
                    addToQuote = form.attr('action').replace("checkout/cart/add", "quoteextension/quote/add"),
                    qtyValidate = form.find('input.qty').attr('data-validate'),
                    data = JSON.parse(form.find('input.qty').attr('data-validate')),
                    qty = form.find('input.qty').val(),
                    maxQty = parseInt(self.options.maxQtyQuote);
                // data['validate-item-quantity']['maxAllowed'] = self.options.maxQtyQuote;
                var validateQuote = JSON.stringify(data);
                form.find('input.qty').attr('data-validate', validateQuote);
                if (self.validateQuote(qty, maxQty)) {
                    self.submitForm(form, addToQuote, dataValidate);
                    if (form.find('div.mage-error').length > 0) {
                        form.find('div.mage-error').remove();
                    }
                } else {
                    if (form.find('div.mage-error').length > 0) {
                        form.find('div.mage-error').remove();
                    }
                    if (qty <= maxQty) {
                        self.submitForm(form, addToQuote, dataValidate);
                    } else {
                        var mess = self.errorQtyQuote(maxQty);
                        form.find('input.qty').after(mess);
                    }
                }
            });
        },

        validateQuote: function (qty, maxQty) {
            return qty <= maxQty;

        },

        errorQtyQuote: function (maxQty) {
            return  '<div for="qty" generated="true" class="mage-error"' +
                ' id="qty-error">'+ $t('The maximum you may quote is %1').replace('%1', maxQty) + '</div>'
        },

        isLoaderEnabled: function() {
            return this.options.processStart && this.options.processStop;
        },

        /**
         * Handler for the form 'submit' event
         *
         * @param {Object} form
         */
        submitForm: function (form, addToQuote, dataValidate) {
            var self = this;
            self.ajaxSubmit(form, addToQuote, dataValidate);
        },

        ajaxSubmit: function(form, addToQuote, dataValidate) {
            var self = this;
            $(self.options.miniquoteSelector).trigger('contentLoading');
            self.disableAddToQuoteButton(form);

            var action = addToQuote;
            var formData = new FormData(form[0]);
            formData.set('quoteextension', '1');
            formData.set('ajax', '1');
            $.ajax({
                url: action,
                data: formData,
                type: 'post',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStart);
                    }
                },
                success: function(res) {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStop);
                    }
                    if (res.backUrl) {
                        window.location = res.backUrl;
                        return;
                    }
                    if (res.messages) {
                        $(self.options.messagesSelector).html(res.messages);
                    }
                    if (res.minicart) {
                        $(self.options.miniquoteSelector).replaceWith(res.minicart);
                        $(self.options.miniquoteSelector).trigger('contentUpdated');
                    }
                    if (res.product && res.product.statusText) {
                        $(self.options.productStatusSelector)
                            .removeClass('available')
                            .addClass('unavailable')
                            .find('span')
                            .html(res.product.statusText);
                    }
                    self.enableAddToQuoteButton(form);
                    if (dataValidate) {
                        form.find('#qty').attr('data-validate', dataValidate);
                    }
                }
            });
        },

        disableAddToQuoteButton: function(form) {
            var addToQuoteButtonTextWhileAdding = this.options.addToQuoteButtonTextWhileAdding || $t('Adding...');
            var addToQuoteButton = $(form).find(this.options.addToQuoteButtonSelector);
            addToQuoteButton.addClass(this.options.addToQuoteButtonDisabledClass);
            addToQuoteButton.find('span').text(addToQuoteButtonTextWhileAdding);
            addToQuoteButton.attr('title', addToQuoteButtonTextWhileAdding);
        },

        enableAddToQuoteButton: function(form) {
            var addToQuoteButtonTextAdded = this.options.addToQuoteButtonTextAdded || $t('Added');
            var self = this,
                addToQuoteButton = $(form).find(this.options.addToQuoteButtonSelector);

            addToQuoteButton.find('span').text(addToQuoteButtonTextAdded);
            addToQuoteButton.attr('title', addToQuoteButtonTextAdded);

            setTimeout(function() {
                var addToQuoteButtonTextDefault = self.options.addToQuoteButtonTextDefault || $t('Add to Quote');
                addToQuoteButton.removeClass(self.options.addToQuoteButtonDisabledClass);
                addToQuoteButton.find('span').text(addToQuoteButtonTextDefault);
                addToQuoteButton.attr('title', addToQuoteButtonTextDefault);
            }, 1000);
        }
    });

    return $.bss.addToQuote;
});
