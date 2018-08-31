<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;

class AfterPlaceOrder extends \GBPrimePay\Payments\Controller\Checkout
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
$this->gbprimepayLogger->addDebug("AfterPlaceOrder execute start//");
}
            $result = $this->jsonFactory->create();
            if ($this->getRequest()->isAjax()) {
                $order = $this->checkoutSession->getLastRealOrder();
                $payment = $order->getPayment();
                $capture = $this->gbprimepayDirect->_capture($order->getPayment(), $order->getBaseGrandTotal());
                if ($capture['id']) {
                    $payment->setAdditionalInformation('id', $capture['id']);
                    if ($capture['resultCode'] === '00') {
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
