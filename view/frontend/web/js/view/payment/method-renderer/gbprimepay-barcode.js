<!--
/**
 * Copyright © 2018 GBPrimePay Payments.
 */
-->
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
    ], function ($, ko, Component, fullScreenLoader, additionalValidators) {
        'use strict';


        $(window).on('hashchange', function() {
              var hash = window.location.hash;
              var selected = $('input[name="payment[method]"]:checked').val();
              if((hash=="#payment") && (selected=="gbprimepay_barcode")){
                $('input[name="payment[method]"]:checked').trigger("click");
              }
        });

        return Component.extend({
            defaults: {
                template: 'GBPrimePay_Payments/payment/gbprimepay_barcode'
            },
            initObservable: function () {
                this.loadBarcodeRender();
                this._super().observe({
                    sayHello: '1'
                });
                var self = this;
                return this;
            },
            getCode: function () {
                return 'gbprimepay_barcode';
            },
            validate: function () {
                return true;
            },
            getInstructionBarcode: function () {
                return window.gbprimepay.instructionbarcode;
            },
            getBarcode: function () {
                return window.gbprimepay.generatebarcode;
            },
            getTitleBarcode:function () {
              return window.gbprimepay.titlebarcode;
            },
            loadBarcodeRender: function () {
                var self = this;
                fullScreenLoader.startLoader();
                self.isPlaceOrderActionAllowed(false);
                $.ajax({
                    type: 'POST',
                    url: window.gbprimepay.afterBarcode,
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            fullScreenLoader.stopLoader();
                            $('div#barcode_display object').attr('data', response.data);
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
            selectPaymentMethod: function() {
              var result = this._super();
              this.loadBarcodeRender();
              return result;
            }
        });
    }
);
