<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;

class EventsBeforeDirect extends \GBPrimePay\Payments\Controller\Checkout
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

              $this->_eventManager->dispatch('gbprimepay_before_direct');
              $_gbpmethod_id = $this->_config->getGBPMethod('GBPMethod');
              $selected = $this->_config->setGBPMethod('selected_direct');
              $payment = \Magento\Framework\App\ObjectManager::getInstance();
              $order = $payment->get('\Magento\Checkout\Model\Cart');
              $amount = $order->getQuote()->getBaseGrandTotal();
              $itemamount = number_format((($amount * 100)/100), 2, '.', '');
              
              $_transaction_id = $this->_config->getGBPTransactionID();
              $_transaction_key = $this->_config->getGBPTransactionKEY();
              $_transaction_amt = $this->_config->getGBPTransactionAMT();
              $isLogin = $this->customerSession->isLoggedIn();
              if ($isLogin) {
              $purchase = array(
                  "id_customer" => $this->customerSession->getCustomerId(),
                  "quoteid" => $_transaction_id,
                  "method" => 'gbprimepay_direct',
                  "status" => 'inactive'
              );
              if($_gbpmethod_id=='selected_installment'){
                $save_purchase = $this->gbprimepayInstallment->_purchaseDataInactive($purchase);
              }else if($_gbpmethod_id=='selected_qrcode'){
                $save_purchase = $this->gbprimepayQrcredit->_purchaseDataInactive($purchase);
              }else if($_gbpmethod_id=='selected_qrcredit'){
                $save_purchase = $this->gbprimepayQrcredit->_purchaseDataInactive($purchase);
              }else{
                $save_purchase = $this->gbprimepayBarcode->_purchaseDataInactive($purchase);              
              }
              }
              return $this->jsonFactory->create()->setData([
                  'success' => true,
                  'transaction_id' => $_transaction_id,
                  'transaction_key' => $_transaction_key,
                  'selected' => 'selected_direct'
              ]);


          } catch (\Exception $exception) {
              return $this->jsonFactory->create()->setData([
                  'success' => false,
                  'error' => true,
                  'message' => $exception->getMessage()
              ]);
          }
      }
  }
