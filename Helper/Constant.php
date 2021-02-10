<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Helper;

class Constant
{
    const TABLE_PREFIX = 'gbprimepay_payments_';

    const URL_3D_SECURE_TEST = 'https://api.globalprimepay.com/v1/tokens/3d_secured';
    const URL_3D_SECURE_LIVE = 'https://api.gbprimepay.com/v1/tokens/3d_secured';

    const URL_API_TEST = 'https://api.globalprimepay.com/v1/tokens';
    const URL_API_LIVE = 'https://api.gbprimepay.com/v1/tokens';

    const URL_CHARGE_TEST = 'https://api.globalprimepay.com/v1/tokens/charge';
    const URL_CHARGE_LIVE = 'https://api.gbprimepay.com/v1/tokens/charge';

    const URL_INSTALLMENT_TEST = 'https://api.globalprimepay.com/v2/installment';
    const URL_INSTALLMENT_LIVE = 'https://api.gbprimepay.com/v2/installment';

    const URL_QRCODE_TEST = 'https://api.globalprimepay.com/gbp/gateway/qrcode';
    const URL_QRCODE_LIVE = 'https://api.gbprimepay.com/gbp/gateway/qrcode';

    const URL_QRCREDIT_TEST = 'https://api.globalprimepay.com/gbp/gateway/qrcredit';
    const URL_QRCREDIT_LIVE = 'https://api.gbprimepay.com/gbp/gateway/qrcredit';

    const URL_QRWECHAT_TEST = 'https://api.globalprimepay.com/gbp/gateway/wechat';
    const URL_QRWECHAT_LIVE = 'https://api.gbprimepay.com/gbp/gateway/wechat';

    const URL_BARCODE_TEST = 'https://api.globalprimepay.com/gbp/gateway/barcode';
    const URL_BARCODE_LIVE = 'https://api.gbprimepay.com/gbp/gateway/barcode';

    const URL_CHECKPUBLICKEY_TEST = 'https://api.globalprimepay.com/checkPublicKey';
    const URL_CHECKPUBLICKEY_LIVE = 'https://api.gbprimepay.com/checkPublicKey';

    const URL_CHECKPRIVATEKEY_TEST = 'https://api.globalprimepay.com/checkPrivateKey';
    const URL_CHECKPRIVATEKEY_LIVE = 'https://api.gbprimepay.com/checkPrivateKey';

    const URL_CHECKCUSTOMERKEY_TEST = 'https://api.globalprimepay.com/checkCustomerKey';
    const URL_CHECKCUSTOMERKEY_LIVE = 'https://api.gbprimepay.com/checkCustomerKey';
}
