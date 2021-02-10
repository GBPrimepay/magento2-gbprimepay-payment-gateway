<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Block\Checkout\View;

use Magento\Framework\Registry;
use Magento\Catalog\Block\Product\Context;

class Config extends \Magento\Framework\View\Element\Template
{
    public $_configHelper;
    protected $cardFactory;
    private $checkoutSession;
    public $customerSession;
    public $countryFactory;
    protected $checkoutRegistry;
    public $localeList;
    

    public function __construct(
        Context $context,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \GBPrimePay\Payments\Model\CardFactory $cardFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Registry $checkoutRegistry,
        \Magento\Framework\Locale\ListsInterface $localeList,
        array $data
    ) {

        $this->customerSession = $customerSession;
        $this->cardFactory = $cardFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_configHelper = $configHelper;
        parent::__construct($context, $data);
        $this->countryFactory = $countryFactory;
        $this->checkoutRegistry = $checkoutRegistry;
        $this->localeList = $localeList;
    }

    public function getDataCard()
    {
        $customer_id = $this->customerSession->getCustomerId();
        $testModel = $this->cardFactory->create()
            ->getCollection()
            ->addFieldToFilter("magento_customer_id", $customer_id)
            ->getData();
        $this->checkFlag = count($testModel);

        return $testModel;
    }
    public function getTransactionID()
    {
        return $this->checkoutSession->getGBPTransactionID();
    }
    public function getTransactionKEY()
    {
        return $this->checkoutSession->getGBPTransactionKEY();
    }
    public function getTransactionAMT()
    {
        return $this->checkoutSession->getGBPTransactionAMT();
    }
    public function getConfigData()
    {
        return $this->_configHelper;
    }

    public function getGenerateQrcode()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
    }

    public function getGenerateQrcredit()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
    }

    public function getGenerateQrwechat()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
    }

    public function getGenerateBarcode()
    {
        return 'data:application/pdf;base64,JVBERi0xLjcKJeLjz9MKMSAwIG9iago8PC9UeXBlL0NhdGFsb2cvUGFnZXMgMiAwIFI+PgplbmRvYmoKMiAwIG9iago8PC9UeXBlL1BhZ2VzL0tpZHNbMyAwIFJdL0NvdW50IDE+PgplbmRvYmoKMyAwIG9iago8PC9UeXBlL1BhZ2UvUGFyZW50IDIgMCBSL01lZGlhQm94WzAgMCA2MTIgNzkyXS9SZXNvdXJjZXM8PD4+Pj4KZW5kb2JqCnhyZWYKMCA0CjAwMDAwMDAwMDAgNjU1MzUgZg0KMDAwMDAwMDAxNSAwMDAwMCBuDQowMDAwMDAwMDYwIDAwMDAwIG4NCjAwMDAwMDAxMTEgMDAwMDAgbg0KdHJhaWxlcgo8PC9TaXplIDQvUm9vdCAxIDAgUj4+CnN0YXJ0eHJlZgoxOTAKJSVFT0YK';
    }

    public function getTransactionDirect() {        
        $transaction['referenceNo'] = $this->checkoutRegistry->registry('referenceNo');
        $transaction['gbpReferenceNo'] = $this->checkoutRegistry->registry('gbpReferenceNo');
        $transaction['amount'] = $this->checkoutRegistry->registry('amount');
        $transaction['orderId'] = $this->checkoutRegistry->registry('orderId');
        $transaction['transaction_form'] = $this->checkoutRegistry->registry('transaction_form');
        $transaction['payStatus'] = $this->checkoutRegistry->registry('payStatus');
        return $transaction;
    }

    public function getTransactionInstallment() {        
        $transaction['referenceNo'] = $this->checkoutRegistry->registry('referenceNo');
        $transaction['gbpReferenceNo'] = $this->checkoutRegistry->registry('gbpReferenceNo');
        $transaction['amount'] = $this->checkoutRegistry->registry('amount');
        $transaction['orderId'] = $this->checkoutRegistry->registry('orderId');
        $transaction['transaction_form'] = $this->checkoutRegistry->registry('transaction_form');
        $transaction['payStatus'] = $this->checkoutRegistry->registry('payStatus');
        return $transaction;
    }

    public function getTransactionQrcode() {
        $transaction['order_generate_qrcode'] = $this->checkoutRegistry->registry('order_generate_qrcode');
        $transaction['order_complete_qrcode'] = $this->checkoutRegistry->registry('order_complete_qrcode');
        $transaction['order_id_qrcode'] = $this->checkoutRegistry->registry('order_id_qrcode');
        $transaction['key_id_qrcode'] = $this->checkoutRegistry->registry('key_id_qrcode');
        return $transaction;
    }

    public function getTransactionQrcredit() {
        $transaction['order_generate_qrcredit'] = $this->checkoutRegistry->registry('order_generate_qrcredit');
        $transaction['order_complete_qrcredit'] = $this->checkoutRegistry->registry('order_complete_qrcredit');
        $transaction['order_id_qrcredit'] = $this->checkoutRegistry->registry('order_id_qrcredit');
        $transaction['key_id_qrcredit'] = $this->checkoutRegistry->registry('key_id_qrcredit');
        return $transaction;
    }

    public function getTransactionQrwechat() {
        $transaction['order_generate_qrwechat'] = $this->checkoutRegistry->registry('order_generate_qrwechat');
        $transaction['order_complete_qrwechat'] = $this->checkoutRegistry->registry('order_complete_qrwechat');
        $transaction['order_id_qrwechat'] = $this->checkoutRegistry->registry('order_id_qrwechat');
        $transaction['key_id_qrwechat'] = $this->checkoutRegistry->registry('key_id_qrwechat');
        return $transaction;
    }

    public function getTransactionBarcode() {
        $transaction['order_generate_barcode'] = $this->checkoutRegistry->registry('order_generate_barcode');
        $transaction['order_complete_barcode'] = $this->checkoutRegistry->registry('order_complete_barcode');
        $transaction['order_id_barcode'] = $this->checkoutRegistry->registry('order_id_barcode');
        $transaction['key_id_barcode'] = $this->checkoutRegistry->registry('key_id_barcode');
        return $transaction;
    }
}
