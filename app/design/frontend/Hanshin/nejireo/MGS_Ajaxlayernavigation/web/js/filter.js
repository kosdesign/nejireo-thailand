define([
    'jquery',
    'domReady!',
    'mage/translate',
    'matchHeight'
], function ($, keyboardHandler,$t, matchHeight) {
    'use strict';
     window.getUrlVars = function() {
        var obj = {};
        var queryString = location.search.substring(1);
        if (!queryString){
            return obj;
        }
        var pairs = queryString.split('&');
        for(var i in pairs){
            var split = pairs[i].split('=');
            obj[decodeURIComponent(split[0])] = decodeURIComponent(split[1]);
        }
        return obj;
    };
    $(document).ready(function () {
        $.getScript( 'https://cdnjs.cloudflare.com/ajax/libs/jquery.pjax/2.0.1/jquery.pjax.min.js',function(){
             window.updatePjaxClick = function(url){
                 window.currentPjaxUrl = url;
                 pjaxLoad(url);
            };
           /* window.clearPjaxClick = function(url){
                pjaxLoad(url);
            };*/
            $(document).on('click', 'div.pages ul li a', function(e) {
                $('body').loader('show');
                e.preventDefault();
                var url = $(this).attr('href');
                pjaxLoad(url);

            });
        });
        function pjaxLoad(url){
            //$('ol.products').fadeOut();
            $.pjax({url:url,fragment:'.update-pjax', container: '.update-pjax',scrollTo:false,async:true, replaceRedirect: false,timeout:0});
            $(document).on('pjax:success', function() {
                $('.lib__mh-items .mh-item').matchHeight({ property: 'min-height' });
                $('body').loader('hide');
            });
           // $('ol.products').fadeIn(65000);
        }
        $(".admin__control-checkbox:checked").each(function () {
            var text = $(this).parents('.action-menu-item').find('.admin__action-multiselect-label').html();
            var html = '<span class="admin__action-multiselect-crumb data_' + $(this).val() + '">' + text + '<button data-value="' + $(this).val() + '" class="action-close close-attr" type="button"><span class="action-close-text">Close</span></button></span>';
            $(this).parents('.admin__action-multiselect-wrap').find('.action-select').html(html);
        });
        var countItemFilter = $('.admin__action-multiselect').find('.admin__action-multiselect-crumb').length;
        if (countItemFilter > 0 ) {
            $('.action-clear .count-item').html('('+countItemFilter+')');
        }

        $('.action-menu').click(function (e) {
            e.stopPropagation();
        });
        $(".action-select-wrap").click(function (e) {
            e.stopPropagation();
            $('.action-select-wrap').not(this).removeClass("_active");
            $(".action-menu").not($(this).find(".action-menu")).removeClass("_active");
            $(".action-select").not( $(this).find(".action-select")).removeClass("_active");
            $(this).toggleClass("_active");
            $(this).find(".action-menu").toggleClass("_active");
            $(this).find(".action-select").toggleClass("_active");
        });

        $(".admin__action-multiselect-menu-inner-item").click(function () {
            var checkBoxes = $(this).find('.admin__control-checkbox');

            checkBoxes.prop("checked", !checkBoxes.prop("checked"));
            if (checkBoxes.is(':checked')) {
                var text = $(this).find('.admin__action-multiselect-label').html();
                var html = '<span class="admin__action-multiselect-crumb data_' + checkBoxes.val() + '">' + text + '<button data-value="' + checkBoxes.val() + '" class="action-close close-attr" type="button"><span  class="action-close-text">Close</span></button></span>';
                $(this).parents('.admin__action-multiselect-wrap').find('.action-select').html(html);
            } else {
                $('.data_' + checkBoxes.val()).remove();
            }
            $(".close-attr").click(function (e) {
                e.stopPropagation();
                var data = $(this).attr('data-value');
                $(this).parent().parent().html('<div class="admin__action-multiselect-text">'+$t('Select...') +'</div>');
                $(':checkbox[value=' + data + ']').prop("checked", false);
                $(":checked.admin__control-checkbox").each(function () {
                    var value = $(this).attr('value');
                    if (value == data) {
                        $(this).removeAttr('checked');
                    }
                })
                $(this).parents('.admin__action-multiselect-crumb').remove();
                var countItemFilter = $('.admin__action-multiselect').find('.admin__action-multiselect-crumb').length;
                if(countItemFilter > 0) {
                    $('.action-clear .count-item').html('('+countItemFilter+')');
                } else {
                    $('.action-clear .count-item').html('');
                }
            });
            var countItemFilter = $('.admin__action-multiselect').find('.admin__action-multiselect-crumb').length;
            if(countItemFilter > 0) {
                $('.action-clear .count-item').html('('+countItemFilter+')');
            } else {
                $('.action-clear .count-item').html('');
            }
        });

        $(".action-update").click(function () {
            $('body').loader('show');
            var items = [];
            var item_test = {};
            var single_value_list = window.getUrlVars();

            $(":checked.admin__control-checkbox").each(function () {
                var attr_val = $(this).val();
                var attr_name = $(this).attr('name');
                if (item_test[attr_name] === undefined) {
                    item_test[attr_name] = [];
                }
                item_test[attr_name].push(attr_val);
                single_value_list[attr_name] = attr_val;
            });
            $(".admin__control-checkbox:not(:checked)").each(function () {
                var attr_name = $(this).attr('name');
                if (single_value_list.hasOwnProperty(attr_name)){
                    var attr_val = $(this).val();
                    if (single_value_list[attr_name] == attr_val){
                        console.log('deleting ' +  attr_name);
                        delete single_value_list[attr_name] ;
                    }
                }
            });

            var url =  window.currentPjaxUrl ? window.currentPjaxUrl : $('#url-page').val() ;
            url = single_value_list ? url.split('?')[0] + "?" + jQuery.param(single_value_list) : url;

            window.updatePjaxClick(url);
            var countItemFilter = $('.admin__action-multiselect').find('.admin__action-multiselect-crumb').length;
            if(countItemFilter > 0) {
                $('.action-clear .count-item').html('('+countItemFilter+')');
            } else {
                $('.action-clear .count-item').html('');
            }
            //window.location = url;
        });

        $(".action-clear").click(function () {
            //window.location = $('#url-page').val();
            $('.action-clear .count-item').html('');
            $('.action-select.admin__action-multiselect').html('<div class="admin__action-multiselect-text">'+$t('Select...') +'</div>');
            $(":checked.admin__control-checkbox").each(function () {
                 $(this).removeAttr('checked');
            });
            var url = $('#url-page').val();
           // window.clearPjaxClick(url);
            window.location = url;
        });

        $('body').click(function () {
            $('.action-select-wrap').removeClass("_active");
            $(".action-menu").removeClass("_active");
            $(".action-select").removeClass("_active");
        });

        $(".action-done").click(function (e) {
            e.stopPropagation();
            $('.action-select-wrap').removeClass("_active");
            $(".action-menu").removeClass("_active");
            $(".action-select").removeClass("_active");
        });

        $(".close-attr").click(function (e) {
            e.stopPropagation();
            var data = $(this).attr('data-value');
            $(this).parent().parent().html('<div class="admin__action-multiselect-text">'+$t('Select...') +'</div>');
            $(':checkbox[value=' + data + ']').prop("checked", false);
            $(":checked.admin__control-checkbox").each(function () {
                var value = $(this).attr('value');
                if (value == data) {
                    $(this).removeAttr('checked');
                }
            });
            $(this).parents('.admin__action-multiselect-crumb').remove();
            var countItemFilter = $('.admin__action-multiselect').find('.admin__action-multiselect-crumb').length;
            if(countItemFilter > 0) {
                $('.action-clear .count-item').html('('+countItemFilter+')');
            } else {
                $('.action-clear .count-item').html('');
            }
        });
    });
});
