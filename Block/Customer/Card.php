<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Block\Customer;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\View\Element\Template;

class Card extends Template
{
    public $_configHelper;
    protected $cardFactory;
    protected $customerSession;


    public function __construct(
        Context $context,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \GBPrimePay\Payments\Model\CardFactory $cardFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data
    ) {

        $this->customerSession = $customerSession;
        $this->cardFactory = $cardFactory;
        $this->_configHelper = $configHelper;
        parent::__construct($context, $data);
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
}
