<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Model;

use Magento\Config\Model\Config\CommentInterface;
use GBPrimePay\Payments\Helper\ConfigHelper;

class Comment implements CommentInterface
{
protected $_config;

public function __construct(
ConfigHelper $configHelper
) {

    // parent::__construct($context);
    $this->_config = $configHelper;
}
    public function getCommentText($elementValue)  //the method has to be named getCommentText
    {
        //do some calculations here
        //return $elementValue . 'Some string based on the calculations';

        return $this->_config->getImageURLs('logo');
    }
}
