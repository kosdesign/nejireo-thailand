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
    'jquery'
], function ($) {
    "use strict";
    return function (config, element) {
        if ($(element).parents('.products-related').length || $(element).parents('.products-upsell').length) {
            $(element).remove();
        }
        addQuoteButton('.action.tocart', $(element), 0);
        
        function addQuoteButton(sel, el, count) {
            if(el.parent().find(sel).length > 0) {
                el.parent().find(sel).parent().append(element);
            }else if (el.parent().find('.hide_price_text').length > 0){
                el.parent().find('.hide_price_text').parent().append(element);
            } else {
                count++;
                if(count < 3) {
                    el = el.parent();
                    addQuoteButton(sel, el, count);
                }
            }
        }
        $(element).show();
    }
});
