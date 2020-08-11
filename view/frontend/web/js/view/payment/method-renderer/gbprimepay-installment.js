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
                      if ((hash == "#payment") && (selected == "gbprimepay_installment")) {
                          $('input[name="payment[method]"]:checked').trigger("click");
                      }
                      clearInterval(generator);
                    }
                }.bind(this), 200);


            $(window).on('hashchange', function() {
                  var hash = window.location.hash;
                  var selected = $('input[name="payment[method]"]:checked').val();
                  if((hash=="#payment") && (selected=="gbprimepay_installment")){
                    $('input[name="payment[method]"]:checked').trigger("click");
                  }
            });

        return Component.extend({
            defaults: {
                template: 'GBPrimePay_Payments/payment/gbprimepay_installment',
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
                return 'gbprimepay_installment';
            },
            validate: function () {
                var self = this;
                var issuers_bankcode = $("select[name='payment[issuers_bankcode]'] option:selected").val();
                if (issuers_bankcode.length < 1) {
                    self.messageContainer.addErrorMessage({
                        message: "Please fill up all the required field!"
                    });
                    return false;
                }
                var issuers_term = $("select[name='payment[issuers_term]'] option:selected").val();
                if (issuers_term.length < 1) {
                    self.messageContainer.addErrorMessage({
                        message: "Please fill up all the required field!"
                    });
                    return false;
                }
                return true;
            },
            getInstructionInstallment: function () {
                return window.gbprimepay.instructionInstallment;
            },
            getImgpathInstallment: function () {
                return window.gbprimepay.imgpathInstallment;
            },
            getTitleInstallment: function () {
                return window.gbprimepay.titleInstallment;
            },
            getTransactionID: function () {
                return window.gbprimepay.transaction_id;
            },
            getTransactionKEY: function () {
                return window.gbprimepay.transaction_key;
            },
            getTransactionAMT: function () {
                return window.gbprimepay.transaction_amt;
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
            createInstallmentRender: function () {
                var self = this;
                var GBPdummy = document.getElementById("GBPdummy");
                var selectList = document.createElement("select");
                selectList.setAttribute("id", "gbprimepay_installment_issuers_dummy");
                selectList.setAttribute('style', 'display: none;');
                selectList.setAttribute('data-bankcode', '#gbprimepay_installment_issuers_bankcode');
                selectList.setAttribute('data-term', '#gbprimepay_installment_issuers_term');
                GBPdummy.appendChild(selectList);
                var renderSelect = '';
                if (self.objissuers.length != 0) {
                    $.each(self.objissuers, function (i,issuers) {
                        $.each(issuers, function (j, obj) {
                        {
                            var counter = 0;
                            $.each(obj.term, function (k, term) {
                                counter++; 
                                if(counter == 1){
                                    renderSelect += "<optgroup label=\"TextValue['" + obj.txt + "','" + obj.id + "']\">";
                                }
                                renderSelect += "<option value='" + term + "'>" + term + " months</option>";
                                
                                if(counter == obj.length){
                                    renderSelect += "</optgroup>";
                                }
                            })
 
                        }
                        });
                    
                });
                }
                $("select[id='gbprimepay_installment_issuers_dummy']").html(renderSelect);
                return true;
                
            },
            loadInstallmentRender: function () {
                var self = this;              
                fullScreenLoader.startLoader();
                self.isPlaceOrderActionAllowed(false);
                $("select[id='gbprimepay_installment_issuers_dummy']").html('');
                $("#GBPdummy").html('');
                $("select[id='gbprimepay_installment_issuers_bankcode']").html('<option value="" data-keep="true">Card issuer bank..</option>');
                $("select[id='gbprimepay_installment_issuers_term']").html('<option value="" data-keep="true">The number of monthly installments..</option>');
                $.ajax({
                    type: 'POST',
                    url: window.gbprimepay.beforeInstallment,
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            fullScreenLoader.stopLoader();
                            self.objissuers = response.transaction_issuers;
                            self.createInstallmentRender();
                            $("input[name='payment[transaction_id]']").val(response.transaction_id);
                            $("input[name='payment[transaction_key]']").val(response.transaction_key);
                            $("input[name='payment[transaction_amt]']").val(response.transaction_amt);
                            $("select[id='gbprimepay_installment_issuers_dummy']").toSelect();
                            $("select[id='gbprimepay_installment_issuers_bankcode']").prop("selectedIndex", 0).val(); 
                            $("select[id='gbprimepay_installment_issuers_bankcode']").trigger("change");
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
            var $orderKey = $("input[name='payment[transaction_key]']").val();            
            var $orderBankcode = $("select[name='payment[issuers_bankcode]'] option:selected").val();          
            var $orderTerm = $("select[name='payment[issuers_term]'] option:selected").val();
            if ($orderId) {
                if (this.item.method == 'gbprimepay_installment') {
                window.location.replace(url.build('gbprimepay/checkout/redirectinstallment/id/' + $orderId + '/bc/' + $orderBankcode + '/tm/' + $orderTerm + '/key/' + $orderKey ));
                }
            }

            },
            selectPaymentMethod: function () {
                var result = this._super();
                this.loadInstallmentRender();
                return result;
            }
        });
    }
);
