<!--
/**
 * Copyright © 2018 GBPrimePay Payments.
 */
-->
require(
    [
        'jquery',
        'Magento_Ui/js/modal/confirm'
    ],
    function ($, confirmation) {
        $('button[id^="del-"]').click(function () {
            var id = $(this).val();
            confirmation({
                title: '',
                content: '<h2>Delete your selected card ?</h2>',
                actions: {
                    confirm: function () {
                        $.ajax({
                            showLoader: true,
                            type: 'POST',
                            url: window.delUrl,
                            dataType: "json",
                            data: {
                                id: id
                            },
                            success: function (response) {
                                if (response.success) {
                                    $('tr[id^="row-' + id + '"]').hide(400,function () {
                                        alert("Your card has been deleted!");
                                    });
                                }
                            },
                            error: function (response) {
                                alert("Has something wrong while deleting your card !");
                            }
                        })
                    },
                    cancel: function () {
                    },
                    always: function () {
                    }
                }
            })
        });
    }
);
