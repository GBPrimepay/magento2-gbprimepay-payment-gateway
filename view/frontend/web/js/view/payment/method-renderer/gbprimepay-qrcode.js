/*
* Copyright © 2020 GBPrimePay Payments.
*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'ko'
    ],function (
        $,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        checkoutData,
        url,
        fullScreenLoader,
        additionalValidators,
        ko
        ) {
        'use strict';

                var generator = setInterval(function () {
                    if ($("input[name='payment[transaction_id]']").length > 0) {
                      var hash = window.location.hash;
                      var selected = $('input[name="payment[method]"]:checked').val();
                      if ((hash == "#payment") && (selected == "gbprimepay_qrcode")) {
                          $('input[name="payment[method]"]:checked').trigger("click");
                      }
                      clearInterval(generator);
                    }
                }.bind(this), 200);


            $(window).on('hashchange', function() {
                  var hash = window.location.hash;
                  var selected = $('input[name="payment[method]"]:checked').val();
                  if((hash=="#payment") && (selected=="gbprimepay_qrcode")){
                    $('input[name="payment[method]"]:checked').trigger("click");
                  }
            });

        return Component.extend({
            defaults: {
                template: 'GBPrimePay_Payments/payment/gbprimepay_qrcode',
                redirectAfterPlaceOrder: false
            },
            initObservable: function () {
                this._super().observe({
                    sayHello: '1'
                });
                var self = this;
                return this;
            },
            getCode: function () {
                return 'gbprimepay_qrcode';
            },
            validate: function () {
                return true;
            },
            getInstructionQrcode: function () {
                return window.gbprimepay.instructionQrcode;
            },
            getLogoQrcode: function () {
                return window.gbprimepay.logoQrcode;
            },
            getTitleQrcode: function () {
                return window.gbprimepay.titleQrcode;
            },
            getTransactionID: function () {
                return window.gbprimepay.transaction_id;
            },
            getTransactionKEY: function () {
                return window.gbprimepay.transaction_key;
            },
            getData: function () {
                var transaction_id = $("input[name='payment[transaction_id]']").val();
                var increment_id = $("input[name='payment[increment_id]']").val();
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_id': transaction_id,
                        'increment_id': increment_id,
                    }
                };
            },
            loadQrcodeRender: function () {
                var self = this;
                fullScreenLoader.startLoader();
                self.isPlaceOrderActionAllowed(false);
                $.ajax({
                    type: 'POST',
                    url: window.gbprimepay.beforeQrcode,
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            fullScreenLoader.stopLoader();
                            $("input[name='payment[transaction_id]']").val(response.transaction_id);
                            $("input[name='payment[transaction_key]']").val(response.transaction_key);
                            self.isPlaceOrderActionAllowed(true);
                        }
                        if (response.error) {
                            fullScreenLoader.stopLoader();
                            $(".loading-mask").hide();
                            self.messageContainer.addErrorMessage({
                                message: response.message
                            });
                        }
                    },
                    error: function (response) {
                        fullScreenLoader.stopLoader();
                        $(".loading-mask").hide();
                        self.messageContainer.addErrorMessage({
                            message: "Error, please try again"
                        });
                    }
                });
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
            afterPlaceOrder: function (orderId) {
            var $orderId = orderId;
            if ($orderId) {
                if (this.item.method == 'gbprimepay_qrcode') {
                window.location.replace(url.build('gbprimepay/checkout/pendingqrcode/id/' + $orderId + '/key/' + $("input[name='payment[transaction_key]']").val()));
                }
            }

            },
            selectPaymentMethod: function () {
                var result = this._super();
                this.loadQrcodeRender();
                return result;
            }
        });
    }
);
