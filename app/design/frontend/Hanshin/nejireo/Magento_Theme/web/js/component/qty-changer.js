/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    $.widget('mage.qtyChanger', {

        /**
         *
         * @private
         */
        _create: function () {
            var self = this;
            self._qtyChanger();
            $('.control.qty').click(function(e){
                e.stopPropagation();
                $(this).addClass('active');
            });
            $('html').click(function(e) {
                self._disableActive();
            });
        },

        _qtyChanger: function () {
            $(".qty-inc").unbind('click').click(function(e){
                e.stopPropagation();
                $(this).parent().parent().addClass('active');
                var inputQty = $(this).parent().parent().find("input.input-text.qty");
                if(inputQty.is(':enabled')){
                    inputQty.val((+inputQty.val() + 1) || 0);
                    inputQty.trigger('change');
                    $(this).focus();
                }
            });
            $(".qty-dec").unbind('click').click(function(e){
                e.stopPropagation();
                $(this).parent().parent().addClass('active');
                var inputQty = $(this).parent().parent().find("input.input-text.qty");
                if(inputQty.is(':enabled')){
                    inputQty.val((inputQty.val() - 1 > 0) ? (inputQty.val() - 1) : 0);
                    inputQty.trigger('change');
                    $(this).focus();
                }
            });
        },

        _disableActive:function () {
            $(".qty-changer").parent().removeClass('active');
        }
    });

    return $.mage.qtyChanger;
});
