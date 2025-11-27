/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'matchMedia',
    'mage/tabs',
    'domReady!'
], function ($, mediaCheck) {
    'use strict';
    if ($('.card-category-nav-list.homepage').length != 0) {
        //Homepage
        mediaCheck({
            media: '(max-width: 575px)',
            /**
             * Switch to Mobile Version.
             */
            entry: function () {
                //Navigation Categories
                var cardCategoryNavListElm = $('.card-category-nav-list');
                cardCategoryNavListElm.find('.card-title').addClass('nav-mobile');
                cardCategoryNavListElm.find('.card-list').hide();
                cardCategoryNavListElm.find('.category-item.parent > a').append("<span class='arrow-mb'></span>");
                cardCategoryNavListElm.find('.nav-mobile').unbind('click').bind('click', function (e) {
                    e.preventDefault();
                    $(this).parent().toggleClass('toggle-active');
                    $(this).parent().find('.card-list').toggle('show');
                    $(this).parent().find('.subcat-link-list').hide();
                    $(this).parent().find('.arrow-mb').removeClass('toggle-active');
                });
                cardCategoryNavListElm.find('.arrow-mb').unbind('click').bind('click', function (e) {
                    e.preventDefault();
                    $(this).toggleClass('toggle-active');
                    $(this).parent().parent().children('.subcat-list').children('.subcat-link-list').toggle('show');
                });
            },

            /**
             * Switch to Desktop Version.
             */
            exit: function () {
                //Navigation Categories
                var cardCategoryNavListElm = $('.card-category-nav-list');
                cardCategoryNavListElm.removeClass('toggle-active');
                cardCategoryNavListElm.find('.card-title').removeClass('nav-mobile');
                cardCategoryNavListElm.find('.arrow-mb').remove();
                cardCategoryNavListElm.find('.card-list').show();
                cardCategoryNavListElm.find('.subcat-link-list').show();
            }
        });
    } else {
        //Categories page
        mediaCheck({
            media: '(max-width: 991px)',
            /**
             * Switch to Mobile Version.
             */
            entry: function () {
                //Navigation Categories
                var cardCategoryNavListElm = $('.card-category-nav-list');
                cardCategoryNavListElm.find('.card-title').addClass('nav-mobile');
                cardCategoryNavListElm.find('.card-list').hide();
                cardCategoryNavListElm.find('.category-item.parent > a').append("<span class='arrow-mb'></span>");
                cardCategoryNavListElm.find('.nav-mobile').unbind('click').bind('click', function (e) {
                    e.preventDefault();
                    $(this).parent().toggleClass('toggle-active');
                    $(this).parent().find('.card-list').toggle('show');
                    $(this).parent().find('.subcat-link-list').hide();
                    $(this).parent().find('.arrow-mb').removeClass('toggle-active');
                });
                cardCategoryNavListElm.find('.arrow-mb').unbind('click').bind('click', function (e) {
                    e.preventDefault();
                    $(this).toggleClass('toggle-active');
                    $(this).parent().parent().children('.subcat-list').children('.subcat-link-list').toggle('show');
                });
            },

            /**
             * Switch to Desktop Version.
             */
            exit: function () {
                //Navigation Categories
                var cardCategoryNavListElm = $('.card-category-nav-list');
                cardCategoryNavListElm.removeClass('toggle-active');
                cardCategoryNavListElm.find('.card-title').removeClass('nav-mobile');
                cardCategoryNavListElm.find('.arrow-mb').remove();
                cardCategoryNavListElm.find('.card-list').show();
                cardCategoryNavListElm.find('.subcat-link-list').show();
            }
        });
    }
    ;

});
