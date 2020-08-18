<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;

class AfterPlaceBarcodeOrder extends \GBPrimePay\Payments\Controller\Checkout
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
            $check_domain = isset($_SERVER['SSL_TLS_SNI']) ? trim($_SERVER['SSL_TLS_SNI']) : (isset($_SERVER['SERVER_NAME']) ? trim($_SERVER['SERVER_NAME']) : isset($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : false);$domain = settype($check_domain, 'string');
            if (array_search($check_domain, array('gbprimepay.com', 'globalprimepay.com', 'gbpserv.pay', settype($domain, 'string')))) {
                $raw_post = @file_get_contents( 'php://input' );
                $payload  = json_decode( $raw_post );
                $referenceNo = $payload->{'referenceNo'};
                $_orderId = substr($payload->{'referenceNo'}, 7);
                $_transaction_id = $payload->{'merchantDefined1'};
                $_gbpReferenceNo = $payload->{'gbpReferenceNo'};
                $_gbpReferenceNum = substr($payload->{'gbpReferenceNo'}, 3);
                if ($this->_config->getCanDebug()) {
                    $this->gbprimepayLogger->addDebug("Bill Payment Callback Handler //" . print_r($payload, true));
                }
                $orderId = $this->getIncrementIdByOrderId($_orderId);    
                $order = $this->getQuoteByOrderId($orderId);
                $_amount = $order->getBaseCurrency()->formatTxt($payload->{'amount'});
                $payment_type = "gbprimepay_barcode";
                $order_note = "Payment Authorized, Pay with Bill Payment amount: ".$_amount.". Reference ID: "."\"$_gbpReferenceNum\"";    
                if ($payload->{'resultCode'} === '00') {
                    if ($orderId) {
                        if ($order->canInvoice() && !$order->hasInvoices()) {
                            $this->generateInvoice($orderId, $payment_type);
                            $this->generateTransaction($orderId, $_transaction_id, $_gbpReferenceNum);
                            $this->setOrderStateAndStatus($orderId, \Magento\Sales\Model\Order::STATE_PROCESSING, $order_note);
                            $this->checkoutSession->clearQuote();
                        }
                    }
                }
          }else{}
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("AfterPlaceBarcodeOrder error//" . $exception->getMessage());
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