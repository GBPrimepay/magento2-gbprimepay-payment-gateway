<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Model;

use GBPrimePay\Payments\Model\GBPrimePayInstallment;
use GBPrimePay\Payments\Model\GBPrimePayQrcode;
use GBPrimePay\Payments\Model\GBPrimePayQrcredit;
use GBPrimePay\Payments\Model\GBPrimePayBarcode;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProvider implements ConfigProviderInterface
{
    protected $methodCodes = [
        GBPrimePayDirect::CODE,
        GBPrimePayInstallment::CODE,
        GBPrimePayQrcode::CODE,
        GBPrimePayQrcredit::CODE,
        GBPrimePayBarcode::CODE
    ];

    protected $methods = [];

    protected $escaper;

    protected $_configHelper;

    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $session,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper
    ) {
        $this->escaper = $escaper;
        $this->_configHelper = $configHelper;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $session;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    public function getConfig()
    {
        return [];
    }
}
