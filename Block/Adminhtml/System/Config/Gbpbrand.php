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

class Gbpbrand extends FormField
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
     * Unset some non-related element parameters
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {

          $html = '<img style="padding:0px 0px 0px 0px" src=' .
              $this->_config->getImageURLs('logo') .
              ' alt="gbprimepay.com">
              <br>
              <label style="padding:0px 0px 10px 0px">GBPrimePay Payments</label>';

        return $html;
    }

}
