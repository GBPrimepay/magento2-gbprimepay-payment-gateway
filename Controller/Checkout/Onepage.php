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

class Onepage  extends \GBPrimePay\Payments\Controller\Checkout implements \Magento\Csp\Api\CspAwareActionInterface
{
  /**
   * Dispatch request
   *
   * @return \Magento\Framework\Controller\ResultInterface\ResponseInterface
   * @throws \Magento\Framework\Exception\NotFoundException
   */
  public function execute()
  {
      // $request = $this->getResponseParams();
      // $request = $this->getRequest()->getParams();

      $postData = $_POST;
      if($postData){
        $referenceNo = $postData['referenceNo'];
        $_orderId = substr($postData['referenceNo'], 7);
        $_gbpReferenceNo = $postData['gbpReferenceNo'];
        $_gbpReferenceNum = substr($postData['gbpReferenceNo'], 3);
      
  //     echo '<pre>';print_r($request, true);echo '</pre>';
  //     echo '<pre>';print_r($postData, true);echo '</pre>';
      
  // if ($this->_config->getCanDebug()) {
  //     $this->gbprimepayLogger->addDebug("\r\n request yHandler //" . print_r($request, true));
  // }

  //   if ($this->_config->getCanDebug()) {
  //       $this->gbprimepayLogger->addDebug("\r\n postData Handler //" . print_r($postData, true));
  //   }
  //   exit;

 




      $resultRedirect = $this->RedirectFactory->create();
      $orderId = $this->getIncrementIdByOrderId($_orderId);
      $order = $this->getQuoteByOrderId($orderId); 
      $payment = $order->getPayment();
      $transaction_form = $payment->getAdditionalInformation("transaction_form");
      if ($postData['resultCode']) {
            if ($postData['resultCode'] === '00') {
              if ($orderId) {
                $_getOrderCompleteStatus = $this->getOrderCompleteStatus($orderId);
                if($_getOrderCompleteStatus != 0){
                    // $resultRedirect->setPath('checkout/onepage/success', array('form_key' => $transaction_form));
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
                    $this->gbprimepayLogger->addDebug("\r\n failureOrder 1//".$transaction_form);
                    // $resultRedirect->setPath('checkout/onepage/failure', array('form_key' => $transaction_form));                    
                    $this->checkoutRegistry->register('referenceNo', $postData['referenceNo'], false);
                    $this->checkoutRegistry->register('gbpReferenceNo', $postData['gbpReferenceNo'], false);
                    $this->checkoutRegistry->register('amount', $postData['amount'], false);
                    $this->checkoutRegistry->register('orderId', $orderId, false);
                    $this->checkoutRegistry->register('transaction_form', $transaction_form, false);
                    $this->checkoutRegistry->register('payStatus', "failure", false);
                  }else{
                    $this->gbprimepayLogger->addDebug("\r\n failureOrder 2//".$transaction_form);
                    // $resultRedirect->setPath('checkout/onepage/failure', array('form_key' => $transaction_form));                    
                    $this->checkoutRegistry->register('referenceNo', '', false);
                    $this->checkoutRegistry->register('gbpReferenceNo', '', false);
                    $this->checkoutRegistry->register('amount', '', false);
                    $this->checkoutRegistry->register('orderId', '', false);
                    $this->checkoutRegistry->register('transaction_form', '', false);
                    $this->checkoutRegistry->register('payStatus', "failure", false);
                  }              
            }
      }else{
            $this->gbprimepayLogger->addDebug("\r\n failureOrder 3//".$transaction_form);
            // $resultRedirect->setPath('checkout/onepage/failure', array('form_key' => $transaction_form));                    
                    $this->checkoutRegistry->register('referenceNo', '', false);
                    $this->checkoutRegistry->register('gbpReferenceNo', '', false);
                    $this->checkoutRegistry->register('amount', '', false);
                    $this->checkoutRegistry->register('orderId', '', false);
                    $this->checkoutRegistry->register('transaction_form', '', false);
                    $this->checkoutRegistry->register('payStatus', "failure", false);
      }
      
    return $this->PageFactory->create();
    }else{
      $this->checkoutSession->restoreQuote();
      $resultRedirect = $this->RedirectFactory->create();
      $resultRedirect->setPath('checkout/cart');
      return $resultRedirect;
    }
  }

  public function modifyCsp(array $appliedPolicies): array
  {
      $appliedPolicies[] = new \Magento\Csp\Model\Policy\FetchPolicy(
          'form-action',
          false,
          ['https://api.gbprimepay.com','https://api.globalprimepay.com'],
          ['https']
      );

      return $appliedPolicies;
  }
}