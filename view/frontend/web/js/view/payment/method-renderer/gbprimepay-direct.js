<!--
/**
 * Copyright © 2018 GBPrimePay Payments.
 */
-->
define(
    [
        'jquery',
        'ko',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success'
    ], function ($, ko, Component, fullScreenLoader, additionalValidators, redirectOnSuccessAction) {
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
              return window.gbprimepay.instructiondirect;
            },
            getTitleDirect:function () {
              return window.gbprimepay.titledirect;
            },
            getLogoDirect:function () {
              return window.gbprimepay.logodirect;
            },
            getData:function () {
                var isSave = $('#is-save').is(":checked");
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'tokenid': this.selectedCard(),
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear(),
                        'cc_ss_issue': this.creditCardSsIssue(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'is_save': isSave ? "1" : "0"
                    }
                };
            },

            afterPlaceOrder: function () {
                var self = this;
                fullScreenLoader.startLoader();
                self.isPlaceOrderActionAllowed(false);
                $.ajax({
                    type: 'POST',
                    url: window.gbprimepay.afterPO,
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            fullScreenLoader.stopLoader();
                            self.isPlaceOrderActionAllowed(true);
                            redirectOnSuccessAction.execute();
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
                      window.console.log(response);
                        fullScreenLoader.stopLoader();
                        self.isPlaceOrderActionAllowed(true);
                        $(".loading-mask").hide();
                        self.messageContainer.addErrorMessage({
                            message: "Error, please try again"
                        });
                    }
                });
            }
        });
    }
);
