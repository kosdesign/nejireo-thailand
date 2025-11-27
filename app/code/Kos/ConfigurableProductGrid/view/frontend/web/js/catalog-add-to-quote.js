define(['jquery', 'mage/validation'], function ($) {
    'use strict';

    var modalWidgetMixin = {
        _bindSubmit: function () {
            var self = this;
            var dataValidate = false;
            this.element.on('click', function (e) {
                var form = $(this).closest("form");
                var addToQuote = form.attr('action').replace("checkout/cart/add", "quoteextension/quote/add");
                if (self.options.validateQty != 1) {
                    dataValidate = form.find('.qty').attr('data-validate');
                    form.find('.qty').removeAttr('data-validate');
                    //self.submitForm(form, addToQuote, dataValidate);
                }
                if (form.validation('isValid')) {
                    self.submitForm(form, addToQuote, dataValidate);
                } else {
                    if (dataValidate) {
                        form.find('.qty').attr('data-validate', dataValidate);
                    }
                }
            });
            $('#hideprice').on('click', this.element, function () {
                var form = $(this).closest("form");
                var addToQuote = form.attr('action').replace("checkout/cart/add", "quoteextension/quote/add");
                if (self.options.validateQty != 1) {
                    dataValidate = form.find('.qty').attr('data-validate');
                    form.find('.qty').removeAttr('data-validate');
                    //self.submitForm(form, addToQuote, dataValidate);
                }
                if (form.validation('isValid')) {
                    self.submitForm(form, addToQuote, dataValidate);
                } else {
                    if (dataValidate) {
                        form.find('.qty').attr('data-validate', dataValidate);
                    }
                }
            })
        },

        ajaxSubmit: function (form, addToQuote, dataValidate) {
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
                beforeSend: function () {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStart);
                    }
                },
                success: function (res) {
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
                        form.find('.qty').attr('data-validate', dataValidate);
                    }
                }
            });
        },
    };

    return function (targetWidget) {
        $.widget('bss.addToQuote', targetWidget, modalWidgetMixin);
        return $.bss.addToQuote;
    };
});
