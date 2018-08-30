<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Helper;

class Constant
{
    const TABLE_PREFIX = 'gbprimepay_payments_';


    const URL_API_TEST = 'https://api.globalprimepay.com/v1/tokens';
    const URL_API_LIVE = 'https://api.gbprimepay.com/v1/tokens';

    const URL_CHARGE_TEST = 'https://api.globalprimepay.com/v1/tokens/charge';
    const URL_CHARGE_LIVE = 'https://api.gbprimepay.com/v1/tokens/charge';

    const URL_QRCODE_TEST = 'https://api.globalprimepay.com/gbp/gateway/qrcode';
    const URL_QRCODE_LIVE = 'https://api.gbprimepay.com/gbp/gateway/qrcode';



    const URL_BARCODE_TEST = 'https://api.globalprimepay.com/gbp/gateway/barcode';
    const URL_BARCODE_LIVE = 'https://api.gbprimepay.com/gbp/gateway/barcode';



    const URL_CHECKPUBLICKEY_TEST = 'https://api.globalprimepay.com/checkPublicKey';
    const URL_CHECKPUBLICKEY_LIVE = 'https://api.gbprimepay.com/checkPublicKey';

    const URL_CHECKPRIVATEKEY_TEST = 'https://api.globalprimepay.com/checkPrivateKey';
    const URL_CHECKPRIVATEKEY_LIVE = 'https://api.gbprimepay.com/checkPrivateKey';

    const URL_CHECKCUSTOMERKEY_TEST = 'https://api.globalprimepay.com/checkCustomerKey';
    const URL_CHECKCUSTOMERKEY_LIVE = 'https://api.gbprimepay.com/checkCustomerKey';



}
