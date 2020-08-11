<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;


use Magento\Framework\Registry;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Api\Data\OrderInterface;
use GBPrimePay\Payments\Helper\Constant;

class Redirect extends \GBPrimePay\Payments\Controller\Checkout
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
          $transactionId = $this->getRequest()->getParam('key');
          $_orderId = $this->getRequest()->getParam('id');
          $orderId = $this->getIncrementIdByOrderId($_orderId);
          $order = $this->getQuoteByOrderId($orderId);      

          $_getEntityId = $order->getEntityId();
          $_getIncrementId = $order->getIncrementId();
          $_getOrderByIncrementId = $this->getOrderIdByIncrementId($_getIncrementId);
          $_getOrderByEntityId = $this->getIncrementIdByOrderId($_getEntityId);
          if (($_orderId == $_getEntityId ) && ($_getIncrementId == $_getOrderByEntityId )) {

                $_transaction_id = $this->_config->getGBPTransactionID();
                $_transaction_key = $this->_config->getGBPTransactionKEY();
                $_transaction_amt = $this->_config->getGBPTransactionAMT();
                $generateitem = $this->_config->getGBPTransactionITEM();

                if ($this->_config->getEnvironment() === 'prelive') {
                    $direct_url = Constant::URL_3D_SECURE_TEST;
                    $direct_publicKey = $this->_config->getTestPublicKey();
                } else {
                    $direct_url = Constant::URL_3D_SECURE_LIVE;
                    $direct_publicKey = $this->_config->getLivePublicKey();
                }

                $direct_gbpReferenceNo = $this->getRequest()->getParam('tf');
                
                $direct_url = rawurlencode($direct_url);
                $direct_publicKey = rawurlencode($direct_publicKey);
                $direct_gbpReferenceNo = rawurlencode($direct_gbpReferenceNo);

                $ordercompletestatus = $this->getOrderCompleteStatus($_getOrderByEntityId);
                if($ordercompletestatus != 0){
                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                }else{
$res =  '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' .
          '<html><head>' .
          '<script type="text/javascript"> function OnLoadEvent() { document.form.submit(); }</script>' .
          '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' .
          '<title>GBPrimePay Payments</title></head>' .
          '<body OnLoad="OnLoadEvent();">' .
          'GBPrimePay, Invoking 3-D Secure Payment, Please Wait ..' .
          '<form name="form" action="'. rawurldecode($direct_url).'" method="post"  target="_top">' .
          '<input type="hidden" name="publicKey" value="'. rawurldecode($direct_publicKey).'">' .
          '<input type="hidden" name="gbpReferenceNo" value="'. rawurldecode($direct_gbpReferenceNo).'">' .
          '<noscript>' .
          '<center><p>Please click button below to Authenticate your card</p><input type="submit" value="Go"/></p></center>' .
          '</noscript>' .
          '</form></body></html>';
echo $res;
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }
        exit;
      } catch (\Exception $exception) {
          if ($this->_config->getCanDebug()) {
              $this->gbprimepayLogger->addDebug("Redirect error //" . $exception->getMessage());
          }
          $this->cancelOrder();
          $this->checkoutSession->restoreQuote();
      }
    }
}