<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;

class DirectPlaceOrder extends \GBPrimePay\Payments\Controller\Checkout
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
            $result = $this->jsonFactory->create();
            if ($this->getRequest()->isAjax()) {
                $order = $this->checkoutSession->getLastRealOrder();
                $payment = $order->getPayment();
                // 3-D Secure Payment
                $secured = $this->gbprimepayDirect->_secured($order->getPayment(), $order->getBaseGrandTotal());
                if ($secured['id']) {
                    $payment->setAdditionalInformation('id', $secured['id']);
                    if ($secured['resultCode'] === '00') {
                        
                        $_orderId = $order->getId();
                        $orderId = $this->getIncrementIdByOrderId($_orderId);    
                        $order = $this->getQuoteByOrderId($orderId);
                        $order->setCanSendNewEmailFlag(false);
                        $order->setCustomerNoteNotify(false); 
                        $amount = $order->getBaseGrandTotal();
                        $itemamount = number_format((($amount * 100)/100), 2, '.', '');
                        $_amount = $order->getBaseCurrency()->formatTxt($itemamount);
                        $payment_type = "gbprimepay_direct";
                        $order_note = "Pending amount: ".$_amount; 
                        if ($orderId) {
                            if ($order->canInvoice() && !$order->hasInvoices()) {
                                $this->setOrderStatePendingStatus($orderId, \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, $order_note);
                            }
                        }

                        return $result->setData([
                            'success' => true,
                            'Tid' => $_orderId,
                            'Tkey' => $secured['id'],
                            'Tref' => $secured['gbpReferenceNo'],
                        ]);
                    } else {
                        return $result->setData([
                            'success' => true
                        ]);
                    }
                } else {
                    if ($this->_config->getCanDebug()) {
                        $this->gbprimepayLogger->addDebug("DirectPlaceOrder error//");
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
                $this->gbprimepayLogger->addDebug("DirectPlaceOrder error//" . $exception->getMessage());
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
