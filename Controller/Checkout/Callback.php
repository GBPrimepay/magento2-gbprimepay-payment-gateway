<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;


class Callback extends \GBPrimePay\Payments\Controller\Checkout
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
            if ($this->_config->getEnvironment() === 'prelive') {

                if (($this->_config->getTestPublicKey() === true) && ($this->_config->getTestSecretKey() === true) && ($this->_config->getTestTokenKey() === true)) {
                }else{
                  return false;
                }

            } else {

                if (($this->_config->getLivePublicKey() === true) && ($this->_config->getLiveSecretKey() === true) && ($this->_config->getLiveTokenKey() === true)) {
                }else{
                  return false;
                }

            }


            $string = file_get_contents("php://input");

            $data = json_decode($string, true);


            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("data-callback //" . print_r($data, true));
            }

            if (isset($data['transactions'])) {
                if ($data['transactions']['type'] === 'payment' && $data['transactions']['state'] === 'successful') {
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
                        $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                            ->setIsCustomerNotified(true);
                        $order->save();
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
