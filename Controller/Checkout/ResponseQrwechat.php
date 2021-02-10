<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;


class ResponseQrwechat extends \GBPrimePay\Payments\Controller\Checkout
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

              $_transactionId = $this->getRequest()->getParam('key');
              $_orderId = $this->getRequest()->getParam('id');
              $orderId = $this->getIncrementIdByOrderId($_orderId);
              $order = $this->getQuoteByOrderId($orderId);
              $ResponseQrwechatStatus = $order->getStatus();
              $ResponseQrwechatState = $order->getState();
            if ($order->hasInvoices()) {
                $ResponseQrwechatStatus = $order->getStatus();
                $ResponseQrwechatState = $order->getState();
                $_getOrderCompleteStatus = $this->getOrderCompleteStatus($orderId);
                if($_getOrderCompleteStatus != 0){
                  return $this->jsonFactory->create()->setData([
                      'success' => true,
                      'gbp_reference_no' => $_getOrderCompleteStatus
                  ]);
                }else{
                  return $this->jsonFactory->create()->setData([
                      'error' => true
                  ]);
                }
            }else{
              return $this->jsonFactory->create()->setData([
                  'error' => true
              ]);
            }
          } catch (\Exception $exception) {
              return $this->jsonFactory->create()->setData([
                  'success' => false,
                  'error' => true,
                  'message' => $exception->getMessage()
              ]);
          }
      }
  }
