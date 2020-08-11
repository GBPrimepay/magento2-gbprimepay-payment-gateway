<?php

/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magento\Framework\Data\Form\Element\AbstractElement;
use \Magento\Backend\Block\Template;
use GBPrimePay\Payments\Helper\Constant;

class BatchTransactionsCallback extends FormField
{

    protected $_config;

    protected $storeManager;

    public function __construct(
        Template\Context $context,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        array $data = []
    ) {
        $this->_config = $configHelper;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself
     *
     * @return \GBPrimePay\Payments\Block\Adminhtml\System\Config\ActiveCallback
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/active.phtml');
        }

        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $check = $this->checkEnable();

        if ($check) {
            $buttonLabel = "Callback actived";
            $class = "callback-actived";
        } else {
            $buttonLabel = "Active callback";
            $class = "";
        }

        $this->addData(
            [
                'add_class' => __($class),
                'button_label' => __($buttonLabel),
                'html_id' => "active_callback_batch_transactions_button",
            ]
        );

        return $this->_toHtml();
    }

    public function checkEnable()
    {
        try {
            $callbackUrl = $this->storeManager->getStore()->getBaseUrl() . 'gbprimepay/checkout/batchTransactionsCallbackResponse';

            if ($this->_config->getEnvironment() === 'prelive') {
                $url = Constant::URL_CALLBACK_TEST;
            } else {
                $url = Constant::URL_CALLBACK;
            }
            $callback = $this->_config->sendCurl("$url", [], 'GET');
            foreach ($callback['callbacks'] as $cb) {
                if ($cb['enabled'] === true && $cb['object_type'] === 'batch_transactions') {
                    if ($cb['url'] === $callbackUrl) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }
}
