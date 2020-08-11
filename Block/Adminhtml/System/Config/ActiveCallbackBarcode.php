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

class ActiveCallbackBarcode extends FormField
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
     * @return \GBPrimePay\Payments\Block\Adminhtml\System\Config\ActiveCallbackBarcode
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
            $buttonLabel = "GBPrimePay Bill Payment : Active";
            $class = "callback-actived";
        } else {
            $buttonLabel = "GBPrimePay Bill Payment : Inactive";
            $class = "";
        }

        $this->addData(
            [
                'add_class' => __($class),
                'button_label' => __($buttonLabel),
                'html_id' => "active_callback_barcode_transaction_button",
                'ajax_url' => $this->_urlBuilder->getUrl('gbprimepay/system_config/activeCallbackBarcode'),
            ]
        );

        return $this->_toHtml();
    }

    public function checkEnable()
    {
        try {
          if ($this->_config->getActiveBarcode()) {
            $callbackUrl = $this->storeManager->getStore()->getBaseUrl() . 'gbprimepay/checkout/callbackbarcode';

            if ($this->_config->getEnvironment() === 'prelive') {
                $url = Constant::URL_CHECKPUBLICKEY_TEST;
            } else {
                $url = Constant::URL_CHECKPUBLICKEY_LIVE;
            }
            $callback = $this->_config->sendPublicCurl("$url", [], 'GET');



                if (!empty($callback['merchantId']) && !empty($callback['initialShop']) && !empty($callback['merchantName'])) {
                        if ($this->_config->getEnvironment() === 'prelive') {
                            $url = Constant::URL_CHECKPRIVATEKEY_TEST;
                        } else {
                            $url = Constant::URL_CHECKPRIVATEKEY_LIVE;
                        }
                        $callback = $this->_config->sendPrivateCurl("$url", [], 'GET');
                            if (!empty($callback['merchantId']) && !empty($callback['initialShop']) && !empty($callback['merchantName'])) {
                              if ($this->_config->getEnvironment() === 'prelive') {
                                  $url = Constant::URL_CHECKCUSTOMERKEY_TEST;
                              } else {
                                  $url = Constant::URL_CHECKCUSTOMERKEY_LIVE;
                              }
                              $callback = $this->_config->sendTokenCurl("$url", [], 'POST');
                                  if (!empty($callback['merchantId']) && !empty($callback['initialShop']) && !empty($callback['merchantName'])) {
                                          return true;
                                  }
                            }
                }


        }
      } catch (\Exception $e) {
            return false;
        }

        return false;
    }
}
