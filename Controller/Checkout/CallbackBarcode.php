<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;


class Callbackbarcode extends \GBPrimePay\Payments\Controller\Checkout
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try {
            if ($this->_config->getEnvironment() === 'prelive') {

                if (($this->_config->getTestPublicKey() === true) && ($this->_config->getTestSecretKey() === true) && ($this->_config->getTestTokenKey() === true)) {
                }else{
                  return false;
                }

            } else {

                if (($this->_config->getLivePublicKey() === true) && ($this->_config->getLiveSecretKey() === true) && ($this->_config->getLiveTokenKey() === true)) {
                }else{
                  return false;
                }

            }


        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("callback ex //" . $exception->getMessage());
            }
        }
    }
}
