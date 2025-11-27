/**

define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
 
        return Component.extend({
            defaults: {
                template: 'Eghl_PaymentMethod/payment/eghlpayment'
            }
        });
    }
);


 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
[
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/place-order',
	'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/url',
],
function (
    $,
    Component,
    placeOrderAction,
	quote,
    selectPaymentMethodAction,
    customer,
    checkoutData,
    additionalValidators,
    url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eghl_PaymentMethod/payment/eghlpayment'
            },
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },

            selectPaymentMethod: function() {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                return true;
            },
			
			in_array: function(needle, haystack) {
				for(var i in haystack) {
					if(haystack[i] == needle) return true;
				}
				return false;
			},

            afterPlaceOrder: function () {
				var telephone = "";
				var payment_desc = "";
				var BaseShipping = 0;
				var DisplayShipping = 0;
				var allowed_currencies = ['MYR','SGD','THB','CNY','PHP','INR','IDR','USD','EUR','AUD','NZD','GBP','JPY','KRW','VND','HKD','TWD','BND'];
				
				/*console.log(window.checkoutConfig);
				debugger;*/
				console.log('urlbuild',url.build(''));
				$.ajax({
					url: url.build('eghlapi/Index/ResponseHandler/?urlType=ordershipping&OrderNumber='+window.checkoutConfig.quoteData.entity_id).replace('/'+window.checkoutConfig.storeCode+'/','/'),
					success: function (result) {
						var data = $.parseJSON(result);
						BaseShipping = data.BaseShipping;
						DisplayShipping = data.DisplayShipping;
					},
					error: function (jqXHR,textStatus,errorThrown ){
						$('#ehl_validation').html('Something went wrong! errorThrown='+errorThrown);
					},
					async: false
				});
				
				if(this.in_array(window.checkoutConfig.quoteData.base_currency_code,allowed_currencies) && this.in_array(window.checkoutConfig.quoteData.store_currency_code,allowed_currencies)){
					var item_count = window.checkoutConfig.quoteItemData.length;
					for(var i=0; i<item_count; i++){
						// if it is last item, do not append comma else do append comma 
						if(i==(item_count-1)){
							payment_desc += '('+window.checkoutConfig.quoteItemData[i].qty+'x)'+window.checkoutConfig.quoteItemData[i].name;
						}
						else{
							payment_desc += '('+window.checkoutConfig.quoteItemData[i].qty+'x)'+window.checkoutConfig.quoteItemData[i].name+',';
						}
					}
					
					if(typeof window.checkoutConfig.customerData.addresses != "undefined"){
						if(window.checkoutConfig.customerData.addresses.length > 0){
							telephone = window.checkoutConfig.customerData.addresses[0].telephone;
						}
					}

					var customer_info = new Object();
					if(isCustomerLoggedIn){
						// logged in
						customer_info.name = window.checkoutConfig.quoteData.customer_firstname+" "+window.checkoutConfig.quoteData.customer_lastname;
						customer_info.email = window.checkoutConfig.quoteData.customer_email;
						customer_info.phone = telephone;
					}
					else{
						// guest checkout
						customer_info.name = quote.billingAddress().firstname+" "+quote.billingAddress().lastname;
						customer_info.email = quote.guestEmail;
						customer_info.phone = quote.billingAddress().telephone;
					}
					
					
					$('<form />')
					  .hide()
					  .attr({ method : "post" })
					  .attr({ action : url.build('eghlgw/')})
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "OrderNumber" })
						.val(window.checkoutConfig.quoteData.entity_id)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "PaymentDesc" })
						.val(payment_desc)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "BaseAmount" })
						.val(window.checkoutConfig.quoteData.base_subtotal_with_discount)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "DisplayAmount" })
						.val(window.checkoutConfig.quoteData.subtotal_with_discount)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "DisplayShipping" })
						.val(DisplayShipping)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "BaseShipping" })
						.val(BaseShipping)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "BaseCurrencyCode" })
						.val(window.checkoutConfig.quoteData.base_currency_code)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "DisplayCurrencyCode" })
						.val(window.checkoutConfig.quoteData.quote_currency_code)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "CustIP" })
						.val(window.checkoutConfig.quoteData.remote_ip)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "CustName" })
						.val(customer_info.name)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "CustEmail" })
						.val(customer_info.email)
					  )
					  .append($('<input />')
						.attr("type","hidden")
						.attr({ "name" : "CustPhone" })
						.val(customer_info.phone)
					  )
					  .append('<input type="submit" />')
					  .appendTo($("body"))
					  .submit();
				}
				else{
					$('#ehl_validation').html('Currency code ('+window.checkoutConfig.quoteData.base_currency_code+' or '+window.checkoutConfig.quoteData.store_currency_code+') is not allowed by eGHL');
					return false;
				}
            },
            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            }


        });
    }
);