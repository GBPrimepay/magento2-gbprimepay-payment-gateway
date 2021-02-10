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

class RedirectInstallment extends \GBPrimePay\Payments\Controller\Checkout
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
                    $installment_url = Constant::URL_INSTALLMENT_TEST;
                    $installment_publicKey = $this->_config->getTestPublicKey();
                    $installment_secret_key = $this->_config->getTestSecretKey();
                } else {
                    $installment_url = Constant::URL_INSTALLMENT_LIVE;
                    $installment_publicKey = $this->_config->getLivePublicKey();
                    $installment_secret_key = $this->_config->getLiveSecretKey();
                }

                $installment_detail = 'Charge for order ' . $_getIncrementId;
                $installment_referenceNo = ''.substr(time(), 4, 5).'00'.$_orderId;
                $installment_bankCode = $this->getRequest()->getParam('bc');
                $installment_term = $this->getRequest()->getParam('tm');
                
                $installment_transaction_form = $this->getRequest()->getParam('form_key');
                $installment_responseUrl = $this->_config->getresponseUrl('response_installment').'form_key/'.$installment_transaction_form;

                $installment_backgroundUrl = $this->_config->getresponseUrl('background_installment');
                $installment_amount = $generateitem['amount'];
                $customer_full_name = $generateitem['customerName'];
                $itemcustomerEmail = $generateitem['customerEmail'];
                $itemcustomerAddress = $generateitem['customerAddress'];
                $itemcustomerTelephone = $generateitem['customerTelephone'];
                $installment_merchantDefined1 = $generateitem['merchantDefined1'];
                $installment_merchantDefined2 = $generateitem['merchantDefined2'];
                $installment_merchantDefined3 = $generateitem['merchantDefined3'];
                $installment_merchantDefined4 = $generateitem['merchantDefined4'];
                $installment_merchantDefined5 = $installment_referenceNo;
                
                $installment_url = rawurlencode($installment_url);
                $installment_publicKey = rawurlencode($installment_publicKey);
                $installment_referenceNo = rawurlencode($installment_referenceNo);
                $installment_responseUrl = rawurlencode($installment_responseUrl);
                $installment_backgroundUrl = rawurlencode($installment_backgroundUrl);
                $installment_detail = rawurlencode($installment_detail);
                $installment_amount = rawurlencode($installment_amount);
                $installment_bankCode = rawurlencode($installment_bankCode);
                $installment_term = rawurlencode($installment_term);
                $installment_merchantDefined1 = rawurlencode($installment_merchantDefined1);
                $installment_merchantDefined2 = rawurlencode($installment_merchantDefined2);
                $installment_merchantDefined3 = rawurlencode($installment_merchantDefined3);
                $installment_merchantDefined4 = rawurlencode($installment_merchantDefined4);
                $installment_merchantDefined5 = rawurlencode($installment_merchantDefined5);
                $installment_secret_key = rawurlencode($installment_secret_key);

                $ordercompletestatus = $this->getOrderCompleteStatus($_getOrderByEntityId);
                if($ordercompletestatus != 0){
                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                }else{
$res =  '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' .
          '<html><head>' .
          '<script type="text/javascript"> function OnLoadEvent() { setTimeout(function(){genChecksum();}, 1000);setTimeout(function(){document.form.submit();}, 1000); }</script>' .
          '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' .
          '<title>GBPrimePay Payments</title></head>' .
          '<body OnLoad="OnLoadEvent();">' .
          'GBPrimePay, Invoking Secure Payment, Please Wait ..' .
          '<form name="form" action="'. rawurldecode($installment_url).'" method="post"  target="_top">' .
          '<input type="hidden" name="publicKey" value="'. rawurldecode($installment_publicKey).'">' .
          '<input type="hidden" name="referenceNo" value="'. rawurldecode($installment_referenceNo).'">' .
          '<input type="hidden" name="responseUrl" value="'. rawurldecode($installment_responseUrl).'">' .
          '<input type="hidden" name="backgroundUrl" value="'. rawurldecode($installment_backgroundUrl).'">' .
          '<input type="hidden" name="detail" value="'. rawurldecode($installment_detail).'">' .
          '<input type="hidden" name="customerName" value="'. rawurldecode($customer_full_name).'">' .
          '<input type="hidden" name="customerEmail" value="'. rawurldecode($itemcustomerEmail).'">' .
          '<input type="hidden" name="customerAddress" value="'. rawurldecode($itemcustomerAddress).'">' .
          '<input type="hidden" name="customerTelephone" value="'. rawurldecode($itemcustomerTelephone).'">' .
          '<input type="hidden" name="amount" value="'. rawurldecode($installment_amount).'">' .
          '<input type="hidden" name="bankCode" value="'. rawurldecode($installment_bankCode).'">' .
          '<input type="hidden" name="term" value="'. rawurldecode($installment_term).'">' .
          '<input type="hidden" name="merchantDefined1" value="'. rawurldecode($installment_merchantDefined1).'">' .
          '<input type="hidden" name="merchantDefined2" value="'. rawurldecode($installment_merchantDefined2).'">' .
          '<input type="hidden" name="merchantDefined3" value="'. rawurldecode($installment_merchantDefined3).'">' .
          '<input type="hidden" name="merchantDefined4" value="'. rawurldecode($installment_merchantDefined4).'">' .
          '<input type="hidden" name="merchantDefined5" value="'. rawurldecode($installment_merchantDefined5).'">' .
          '<input type="hidden" name="checksum" value="">' .
          '<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.min.js"></script>' .
          '<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/hmac-sha256.min.js"></script>' .
          '<script>' .
          'function genChecksum(){' .
          'var hash = CryptoJS.HmacSHA256(' .
          'document.getElementsByName("amount")[0].value +' .
          'document.getElementsByName("referenceNo")[0].value +' .
          'document.getElementsByName("responseUrl")[0].value +' .
          'document.getElementsByName("backgroundUrl")[0].value +' .
          'document.getElementsByName("bankCode")[0].value +' .
          'document.getElementsByName("term")[0].value ,' .
          '"'. rawurldecode($installment_secret_key).'");' .
          'document.getElementsByName("checksum")[0].value = hash;' .
          '}' .
          '</script>' .
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
              $this->gbprimepayLogger->addDebug("RedirectInstallment error //" . $exception->getMessage());
          }
          $this->cancelOrder();
          $this->checkoutSession->restoreQuote();
      }
    }
}