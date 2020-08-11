<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;


class EventsBeforeInstallment extends \GBPrimePay\Payments\Controller\Checkout
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
              $this->_eventManager->dispatch('gbprimepay_before_installment');
              $selected = $this->_config->setGBPMethod('selected_installment');
              $payment = \Magento\Framework\App\ObjectManager::getInstance();
              $order = $payment->get('\Magento\Checkout\Model\Cart');
              $amount = $order->getQuote()->getBaseGrandTotal();
              $itemamount = number_format((($amount * 100)/100), 2, '.', '');
              
              $_transaction_id = $this->_config->getGBPTransactionID();
              $_transaction_key = $this->_config->getGBPTransactionKEY();
              $_transaction_amt = $this->_config->getGBPTransactionAMT();
              $isLogin = $this->customerSession->isLoggedIn();
              if ($isLogin) {
              $currentdate = date('Y-m-d H:i');
              $purchase = array(
                  "id_customer" => $this->customerSession->getCustomerId(),
                  "quoteid" => $_transaction_id,
                  "method" => 'gbprimepay_installment',
                  "status" => 'active'
              );
              $save_purchase = $this->gbprimepayInstallment->_purchaseData($purchase);
              }
                $objIssuers = ['issuers' => [],];
                $check_kasikorn_it = $this->_config->check_term_regex($this->_config->getTermInstallment('kasikorn'),'kasikorn');
                $check_krungthai_it = $this->_config->check_term_regex($this->_config->getTermInstallment('krungthai'),'krungthai');
                $check_thanachart_it = $this->_config->check_term_regex($this->_config->getTermInstallment('thanachart'),'thanachart');
                $check_ayudhya_it = $this->_config->check_term_regex($this->_config->getTermInstallment('ayudhya'),'ayudhya');
                $check_firstchoice_it = $this->_config->check_term_regex($this->_config->getTermInstallment('firstchoice'),'firstchoice');
                $check_scb_it = $this->_config->check_term_regex($this->_config->getTermInstallment('scb'),'scb');

                $obj_kasikorn_it = $this->_config->obj_term_regex($check_kasikorn_it,'kasikorn',$itemamount);
                $obj_krungthai_it = $this->_config->obj_term_regex($check_krungthai_it,'krungthai',$itemamount);
                $obj_thanachart_it = $this->_config->obj_term_regex($check_thanachart_it,'thanachart',$itemamount);
                $obj_ayudhya_it = $this->_config->obj_term_regex($check_ayudhya_it,'ayudhya',$itemamount);
                $obj_firstchoice_it = $this->_config->obj_term_regex($check_firstchoice_it,'firstchoice',$itemamount);
                $obj_scb_it = $this->_config->obj_term_regex($check_scb_it,'scb',$itemamount);

                if(!empty($obj_kasikorn_it)){
                    $objIssuers['issuers']['kasikorn'] = $obj_kasikorn_it['kasikorn'];
                }
                if(!empty($obj_krungthai_it)){
                    $objIssuers['issuers']['krungthai'] = $obj_krungthai_it['krungthai'];
                }
                if(!empty($obj_thanachart_it)){
                    $objIssuers['issuers']['thanachart'] = $obj_thanachart_it['thanachart'];
                }
                if(!empty($obj_ayudhya_it)){
                    $objIssuers['issuers']['ayudhya'] = $obj_ayudhya_it['ayudhya'];
                }
                if(!empty($obj_firstchoice_it)){
                    $objIssuers['issuers']['firstchoice'] = $obj_firstchoice_it['firstchoice'];
                }
                if(!empty($obj_scb_it)){
                    $objIssuers['issuers']['scb'] = $obj_scb_it['scb'];
                }
              return $this->jsonFactory->create()->setData([
                  'success' => true,
                  'transaction_id' => $_transaction_id,
                  'transaction_key' => $_transaction_key,
                  'transaction_amt' => $_transaction_amt,
                  'transaction_issuers' => $objIssuers,
                  'selected' => 'selected_installment'
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
