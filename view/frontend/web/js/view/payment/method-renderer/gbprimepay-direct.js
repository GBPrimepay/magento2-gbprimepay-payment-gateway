/*
* Copyright © 2020 GBPrimePay Payments.
*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'mage/url',
        'ko'
    ], function ($,
        Component,
        fullScreenLoader,
        additionalValidators,
        redirectOnSuccessAction,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        url,
        ko
        ) {
        'use strict';

        var card = function (newCard) {
            return {
                id: ko.observable(newCard.id),
                last4: ko.observable(newCard.last4)
            }
        };

        return Component.extend({
            defaults: {
                template: 'GBPrimePay_Payments/payment/gbprimepay_direct',
                redirectAfterPlaceOrder: false
            },
            isFormVisible: '',
            selectedCard: '',
            savedCards: window.gbprimepay.savedCards,
            newSavedCards: [],

            initObservable: function () {
                this._super().observe({
                    isFormVisible: 1,
                    selectedCard: 0,
                    newSavedCards: []
                });
                var self = this;

                var mappedCard = $.map(this.savedCards, function (savedCard) {
                    var itemObj = new card(savedCard);
                    itemObj.parentObj = self;
                    return itemObj;
                });

                self.newSavedCards(mappedCard);
                this.selectedCard.subscribe(function (value) {
                    var intValue = parseInt(value);
                    self.isFormVisible(!intValue);
                });

                return this;
            },

            hasCard: function () {
                return this.savedCards.length > 1;
            },

            getCode: function () {
                return 'gbprimepay_direct';
            },

            isActive: function () {
                var self = this;
                return true;
            },
            getInstructionDirect:function () {
              return window.gbprimepay.instructionDirect;
            },
            getTitleDirect:function () {
              return window.gbprimepay.titleDirect;
            },
            getLogoDirect:function () {
              return window.gbprimepay.logoDirect;
            },
            getTransactionID: function () {
                return window.gbprimepay.transaction_id;
            },
            getTransactionKEY: function () {
                return window.gbprimepay.transaction_key;
            },
            getFormKey: function () {
                return window.checkoutConfig.formKey;
            },
            getData:function () {
                var isSave = $('[name="payment[save]"]').is(':checked') ? 1 : 0;
                var transaction_form = $("input[name='form_key']").val();
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'tokenid': this.selectedCard(),
                        'transaction_form': transaction_form,
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear(),
                        'cc_ss_issue': this.creditCardSsIssue(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'is_save': isSave
                    }
                };
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
                var self = this;
                fullScreenLoader.startLoader();
                self.isPlaceOrderActionAllowed(false);
                $.ajax({
                    type: 'POST',
                    url: window.gbprimepay.directPO,
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            var $orderId = orderId; 
                            var $orderFormkey = $("input[name='form_key']").val();          
                            if ($orderId == response.Tid) {
setTimeout(function () {
    window.location.replace(url.build('gbprimepay/checkout/redirect/id/' + response.Tid + '/tf/' + response.Tref + '/key/' + response.Tkey ));
}, 200);                                
                            }
                        }
                        if (response.error) {
                            fullScreenLoader.stopLoader();
                            self.isPlaceOrderActionAllowed(true);
                            $(".loading-mask").hide();
                            self.messageContainer.addErrorMessage({
                                message: response.message
                            });
                        }
                    },
                    error: function (response) {
                        fullScreenLoader.stopLoader();
                        self.isPlaceOrderActionAllowed(true);
                        $(".loading-mask").hide();
                        self.messageContainer.addErrorMessage({
                            message: "Error, please try again"
                        });
                    }
                });
            },
            loadDirectRender: function () {
                var self = this;
                fullScreenLoader.startLoader();
                self.isPlaceOrderActionAllowed(false);
                $.ajax({
                    type: 'POST',
                    url: window.gbprimepay.beforeDirect,
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
            selectPaymentMethod: function() {
              var result = this._super();
              this.loadDirectRender();
              return result;
            }
        });
    }
);
