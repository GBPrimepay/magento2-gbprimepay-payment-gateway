<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Block\Checkout\View;

use Magento\Catalog\Block\Product\Context;

class Config extends \Magento\Framework\View\Element\Template
{
    public $_configHelper;
    protected $cardFactory;
    private $checkoutSession;
    public $customerSession;
    public $countryFactory;
    public $localeList;

    public function __construct(
        Context $context,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \GBPrimePay\Payments\Model\CardFactory $cardFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Locale\ListsInterface $localeList,
        array $data
    ) {

        $this->customerSession = $customerSession;
        $this->cardFactory = $cardFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_configHelper = $configHelper;
        parent::__construct($context, $data);
        $this->countryFactory = $countryFactory;
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

    public function getConfigData()
    {
        return $this->_configHelper;
    }

    public function getGenerateQrcode()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
    }

    public function getGenerateBarcode()
    {
        // return $this->checkoutSession->getGenerateBarcode();
        return 'data:application/pdf;base64,JVBERi0xLjcKJeLjz9MKMSAwIG9iago8PC9UeXBlL0NhdGFsb2cvUGFnZXMgMiAwIFI+PgplbmRvYmoKMiAwIG9iago8PC9UeXBlL1BhZ2VzL0tpZHNbMyAwIFJdL0NvdW50IDE+PgplbmRvYmoKMyAwIG9iago8PC9UeXBlL1BhZ2UvUGFyZW50IDIgMCBSL01lZGlhQm94WzAgMCA2MTIgNzkyXS9SZXNvdXJjZXM8PD4+Pj4KZW5kb2JqCnhyZWYKMCA0CjAwMDAwMDAwMDAgNjU1MzUgZg0KMDAwMDAwMDAxNSAwMDAwMCBuDQowMDAwMDAwMDYwIDAwMDAwIG4NCjAwMDAwMDAxMTEgMDAwMDAgbg0KdHJhaWxlcgo8PC9TaXplIDQvUm9vdCAxIDAgUj4+CnN0YXJ0eHJlZgoxOTAKJSVFT0YK';
    }
}
