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
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Csp\Api\CspAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use GBPrimePay\Payments\Helper\Constant;
use GBPrimePay\Payments\Controller\Checkout\CsrfValidator;

class PendingDirect extends \GBPrimePay\Payments\Controller\Checkout
{
  /**
   * Dispatch request
   *
   * @return \Magento\Framework\Controller\ResultInterface\ResponseInterface
   * @throws \Magento\Framework\Exception\NotFoundException
   */
  public function execute()
  {
      $postData = $_POST;
      if(isset($postData['resultCode'])){
        $referenceNo = $postData['referenceNo'];
        $_orderId = substr($postData['referenceNo'], 7);
        $_gbpReferenceNo = $postData['gbpReferenceNo'];
        $_gbpReferenceNum = substr($postData['gbpReferenceNo'], 3);
        $resultRedirect = $this->RedirectFactory->create();
        $orderId = $this->getIncrementIdByOrderId($_orderId);
        $order = $this->getQuoteByOrderId($orderId); 
        $_getCustomerId = $order->getCustomerId();
        $payment = $order->getPayment();
        $transaction_form = $payment->getAdditionalInformation("transaction_form");
      if ($postData['resultCode']) {
            $isLogin = $this->customerSession->isLoggedIn();
            if ($isLogin) {
            }else{
              if(!empty($_getCustomerId)){
                $transaction_form = $this->reloadCustomerId($payment, $_getCustomerId, $transaction_form);
              }
            }
            if ($postData['resultCode'] === '00') {
              if ($orderId) {
                $_getOrderCompleteStatus = $this->getOrderCompleteStatus($orderId);
                if($_getOrderCompleteStatus != 0){
                    $this->checkoutRegistry->register('referenceNo', $postData['referenceNo'], false);
                    $this->checkoutRegistry->register('gbpReferenceNo', $postData['gbpReferenceNo'], false);
                    $this->checkoutRegistry->register('amount', $postData['amount'], false);
                    $this->checkoutRegistry->register('orderId', $orderId, false);
                    $this->checkoutRegistry->register('transaction_form', $transaction_form, false);
                    $this->checkoutRegistry->register('payStatus', "success", false);
                }
              }
            }else{
                  if ($orderId) {
                    $order_note = "Payment Failure, Transaction cannot be authorized";
                    $this->failureOrder($orderId, "canceled", $order_note);                   
                    $this->checkoutRegistry->register('referenceNo', $postData['referenceNo'], false);
                    $this->checkoutRegistry->register('gbpReferenceNo', $postData['gbpReferenceNo'], false);
                    $this->checkoutRegistry->register('amount', $postData['amount'], false);
                    $this->checkoutRegistry->register('orderId', $orderId, false);
                    $this->checkoutRegistry->register('transaction_form', $transaction_form, false);
                    $this->checkoutRegistry->register('payStatus', "failure", false);
                  }else{             
                    $this->checkoutRegistry->register('referenceNo', '', false);
                    $this->checkoutRegistry->register('gbpReferenceNo', '', false);
                    $this->checkoutRegistry->register('amount', '', false);
                    $this->checkoutRegistry->register('orderId', '', false);
                    $this->checkoutRegistry->register('transaction_form', '', false);
                    $this->checkoutRegistry->register('payStatus', "failure", false);
                  }              
            }
      }else{           
                    $this->checkoutRegistry->register('referenceNo', '', false);
                    $this->checkoutRegistry->register('gbpReferenceNo', '', false);
                    $this->checkoutRegistry->register('amount', '', false);
                    $this->checkoutRegistry->register('orderId', '', false);
                    $this->checkoutRegistry->register('transaction_form', '', false);
                    $this->checkoutRegistry->register('payStatus', "failure", false);
      }
      
    return $this->PageFactory->create();
    }else{
      $resultRedirect = $this->RedirectFactory->create();
      $resultRedirect->setPath('/');
      return $resultRedirect;
    }
  }

  public function modifyCsp(array $appliedPolicies): array
  {
      $appliedPolicies[] = new \Magento\Csp\Model\Policy\FetchPolicy(
          'form-action',
          false,
          ['https://api.gbprimepay.com/web/ktc_gateway/success','https://api.gbprimepay.com/web/ktc_gateway/cancel','https://api.gbprimepay.com/web/ktc_gateway/fail','https://api.gbprimepay.com/web/bbl_gateway/receive/goback/success','https://api.gbprimepay.com/web/bbl_gateway/receive/goback/fail','https://api.gbprimepay.com/web/bbl_gateway/receive/goback/cancel','https://api.gbprimepay.com/web/thanachat_gateway/receive/go_back','https://api.gbprimepay.com/web/scb_gateway/receive/realtime','https://api.gbprimepay.com/web/gateway/receive/goback','https://api.gbprimepay.com/gbp/gateway/receive/goback','https://api.globalprimepay.com/web/thanachat_gateway/receive/go_back'],
          ['https']
      );

      return $appliedPolicies;
  }
}