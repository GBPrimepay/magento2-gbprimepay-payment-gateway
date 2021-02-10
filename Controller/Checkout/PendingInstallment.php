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
use Magento\Checkout\Model\Session;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use GBPrimePay\Payments\Helper\Constant;
use GBPrimePay\Payments\Controller\Checkout\CsrfValidator;

class PendingInstallment extends \GBPrimePay\Payments\Controller\Checkout implements \Magento\Csp\Api\CspAwareActionInterface
{
  /**
   * Dispatch request
   *
   * @return \Magento\Framework\Controller\ResultInterface\ResponseInterface
   * @throws \Magento\Framework\Exception\NotFoundException
   */
  public function execute()
  {
      // $postData = $_POST;
      $postData = $this->getRequest()->getParams();
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
        $transaction_form_additional = $payment->getAdditionalInformation("transaction_form");
      if ($postData['resultCode']) {
            $isLogin = $this->customerSession->isLoggedIn();
            if ($isLogin) {
            }else{
              if(!empty($_getCustomerId)){
                $transaction_form = $this->reloadCustomerId($payment, $_getCustomerId, $transaction_form_additional);
              }
            }
            if ($postData['resultCode'] === '00') {
              if ($orderId) {
                $_getOrderCompleteStatus = $this->getOrderCompleteStatus($orderId);
                if($_getOrderCompleteStatus != 0){
                            
                    $this->checkoutSession->setLastQuoteId($order->getQuoteId());
                    $this->checkoutSession->setLastOrderId($order->getId());
                    $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
                    $this->checkoutSession->setLastOrderStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $this->checkoutSession->setLastQuoteId($order->getQuoteId())->setLastSuccessQuoteId($order->getQuoteId());
                    
                    if(empty($transaction_form)){
                      $transaction_form = $transaction_form_additional;
                    }

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
                    if(empty($transaction_form)){
                      $transaction_form = $transaction_form_additional;
                    }
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
          ['https://api.gbprimepay.com/web/krungsri_gateway/receive/success','https://api.gbprimepay.com/web/ktc_gateway/success','https://api.gbprimepay.com/web/ktc_gateway/cancel','https://api.gbprimepay.com/web/ktc_gateway/fail','https://api.gbprimepay.com/web/bbl_gateway/receive/goback/success','https://api.gbprimepay.com/web/bbl_gateway/receive/goback/fail','https://api.gbprimepay.com/web/bbl_gateway/receive/goback/cancel','https://api.gbprimepay.com/web/thanachat_gateway/receive/go_back','https://api.gbprimepay.com/web/scb_gateway/receive/goback','https://api.gbprimepay.com/web/gateway/receive/goback','https://api.gbprimepay.com/gbp/gateway/receive/goback','https://api.globalprimepay.com/web/scb_gateway/receive/goback','https://api.globalprimepay.com/gbp/gateway/pay/scbInstallment'],
          ['https']
      );

      return $appliedPolicies;
  }
}