<!--
/**
 * Copyright © 2018 GBPrimePay Payments.
 */
-->
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        $,
        Component,
        rendererList
    ) {
        'use strict';

        var methods = [
            {
                type: 'gbprimepay_direct',
                component: 'GBPrimePay_Payments/js/view/payment/method-renderer/gbprimepay-direct'
            },
            {
                type: 'gbprimepay_qrcode',
                component: 'GBPrimePay_Payments/js/view/payment/method-renderer/gbprimepay-qrcode'
            },
            {
                type: 'gbprimepay_barcode',
                component: 'GBPrimePay_Payments/js/view/payment/method-renderer/gbprimepay-barcode'
            }
        ];

        $.each(methods, function (k, method) {
            rendererList.push(method);
        });

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
