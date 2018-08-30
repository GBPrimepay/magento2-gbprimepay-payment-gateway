<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;

class BatchTransactionsCallbackResponse extends \GBPrimePay\Payments\Controller\Checkout
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
            $env = $this->_config->getEnvironment();

            $string = file_get_contents("php://input");

            $data = json_decode($string, true);
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("callback_batch_transaction //" . print_r($data, true));
            }
            if (isset($data['resultCode'])) {
                if ($data['resultCode'] === '00') {
                    $itemId = $data['transactions']['account_id'];
                    $item = MagentoPay::Item()->get($itemId);
                    $orderId = $item['custom_descriptor'];
                    $order = $this->orderFactory->create()->loadByIncrementId($orderId);
                    $order->setCanSendNewEmailFlag(true);
                    $this->sendEmailCustomer($order);
                    /** @var \Magento\Sales\Model\Order\Payment $payment */
                    $payment = $order->getPayment();
                    if (!$payment->getAdditionalInformation('isCapture')) {
                        $invoice = $order->prepareInvoice();
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        $invoice->register();
                        $transaction = $this->_objectManager->create('\\Magento\\Framework\\DB\\Transaction');
                        $transaction->addObject($invoice)
                            ->addObject($invoice->getOrder())
                            ->save();
                        $invoiceSender = $this->_objectManager->create('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
                        $invoiceSender->send($invoice);
                        $order->addStatusHistoryComment("The Batch Transaction has been processed successfulThe Batch Transaction has been processed successful");
                        $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                            ->setIsCustomerNotified(true);
                        $order->save();
                    }
                } else {
                    if ($data['transactions']['type'] == 'payment' &&
                        (
                            $data['transactions']['state'] == 'invalid_account_details' ||
                            $data['transactions']['state'] == 'failed_direct_debit' ||
                            $data['transactions']['state'] == 'errored'
                        )
                    ) {
                        $itemId = $data['transactions']['account_id'];
                        $item = MagentoPay::Item()->get($itemId);
                        $orderId = $item['custom_descriptor'];
                        $order = $this->orderFactory->create()->loadByIncrementId($orderId);
                        $order->cancel();
                        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                        $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                        if ($data['transactions']['state'] == 'invalid_account_details') {
                            $order->addStatusHistoryComment("The Batch Transaction has failed due to incorrect bank account details.");
                        }
                        if ($data['transactions']['state'] == 'failed_direct_debit') {
                            $order->addStatusHistoryComment("The Batch Transaction has been dishonoured due to insufficient funds or other restrictions.");
                        }
                        if ($data['transactions']['state'] == 'errored') {
                            $order->addStatusHistoryComment("The Batch Transaction contained an error and will no longer be processed.");
                        }
                        $order->save();

                        $payment = $order->getPayment();
                        $payment->setStatus('Payment EXCEPTION');
                        $payment
                            ->setShouldCloseParentTransaction(1)
                            ->setIsTransactionClosed(1);
                        $this->gbprimepayLogger->critical("payment exception from bank");
                    }
                }
            }
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("callback ex //" . $exception->getMessage());
            }
        }
    }
}
