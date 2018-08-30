<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as CollectionFactory;

class AfterPlaceQrcodeOrder extends \GBPrimePay\Payments\Controller\Checkout
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

if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("AfterPlaceQrcodeOrder execute start//");
}
            $check_domain = isset($_SERVER['SSL_TLS_SNI']) ? trim($_SERVER['SSL_TLS_SNI']) : (isset($_SERVER['SERVER_NAME']) ? trim($_SERVER['SERVER_NAME']) : isset($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : false);

            if (array_search($check_domain, array('gbprimepay.com', 'globalprimepay.com', 'gbpserv.pay'))) {




  if ($this->getRequest()->getPostValue()) {
  $_post = $this->getRequest()->getPostValue();
  $capture = $this->gbprimepayQrcode->_capture($_post['amount'], $_post);

  // echo $_post['gbpReferenceNo'];
  // echo "<br>true-<pre>";


    // echo "<br>2true-<pre>";
    // print_r($orderId);
    // echo "<br><br>";
    // print_r($capture);
    exit;

    $result = $this->jsonFactory->create();
        if ($capture['id']) {
            if ($capture['resultCode'] === '00') {
                $orderId = $capture['orderCode'];
                $order = $this->orderFactory->create()->loadByIncrementId($orderId);
                print_r($orderId);
                exit;
                $order->setCanSendNewEmailFlag(true);
                $this->sendEmailCustomer($order);
                $invoice = $order->prepareInvoice();
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $transaction = $objectManager->create('\\Magento\\Framework\\DB\\Transaction');
                $transaction->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();

                $invoiceSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
                $invoiceSender->send($invoice);
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice #%1.', $invoice->getId())
                )
                    ->setIsCustomerNotified(true);
                $order->save();

                return $result->setData([
                    'success' => true
                ]);
            } else {
                $order->addStatusHistoryComment(__('Your order was on pending state!'));
                $this->_messageManager->addNoticeMessage("Your order was on pending state!");
                $order->save();

                return $result->setData([
                    'success' => true
                ]);
            }
        } else {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("afterPO//");
            }

            return $result->setData([
                'success' => false,
                'error' => true,
                'message' => 'Something went wrong. Please try again!'
            ]);
        }
















  }













          }else{}


        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("after PO //" . $exception->getMessage());
            }
            $this->cancelOrder();
            $this->checkoutSession->restoreQuote();

            return $this->jsonFactory->create()->setData([
                'success' => false,
                'error' => true,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
