<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Model\ResourceModel;

use GBPrimePay\Payments\Helper\Constant as Constant;

class Purchase extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $_prefix = Constant::TABLE_PREFIX;
        $this->_init($_prefix . 'purchase', 'id');
    }
}
