define([
    'jquery',
    'mage/mage',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    var width = $(window).width();
    if (width < 992) {
        if(!($('.head-actions-mobile > .miniquote-wrapper').length)){
            $('<div class="miniquote-wrapper"></div>').appendTo('.head-actions-mobile');
            $(".miniquote-wrapper a.action.showquote").clone().appendTo('.head-actions-mobile .miniquote-wrapper');
        }
        if(!($('.head-actions-mobile > .minicart-wrapper').length)){
            $('<div data-block="minicart" class="minicart-wrapper"></div>').appendTo('.head-actions-mobile');
            $(".minicart-wrapper a.action.showcart").clone().appendTo('.head-actions-mobile .minicart-wrapper');
        }
        $(".card-switching").click(function () {
            $(this).find('.cs-options').toggleClass('active');
        });
        $(".head-contact-center .cs-close").click(function () {
            $('.cs-options').removeClass('active');
        });
        $(".head-contact-center .cs-options ul > li").click(function () {
            $(this).toggleClass('active');
        });

        $(".cs-options .content").click(function (e) {
            e.stopPropagation();
        });

        $(".cs-options").click(function (e) {
            e.stopPropagation();
        });
    }

    $('.header-mobile').click(function () {
        $(this).toggleClass('active');
        $('.header-content-mobile').toggleClass('active');
    });

    $('.header-content-mobile').click(function (e) {
        e.stopPropagation();
    });
});
