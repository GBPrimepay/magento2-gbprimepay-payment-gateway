<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;

class AfterPlaceDirectOrder extends \GBPrimePay\Payments\Controller\Checkout
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
$this->gbprimepayLogger->addDebug("AfterPlaceDirectOrder execute start//");
}

            $result = $this->jsonFactory->create();
            if ($this->getRequest()->isAjax()) {
                $order = $this->checkoutSession->getLastRealOrder();
                $payment = $order->getPayment();
                $secured = $this->gbprimepayDirect->_secured($order->getPayment(), $order->getBaseGrandTotal());
                if ($secured['id']) {
                    $payment->setAdditionalInformation('id', $secured['id']);
                    if ($secured['resultCode'] === '00') {
                      if (!empty($callback['gbpReferenceNo']) && ($otpCode == 'Y')) {

                        // $account_settings = get_option('gbprimepay_account_settings');
  
                        // $otp_url = gbp_instances('URL_3D_SECURE_LIVE');
                        // $otp_publicKey = $account_settings['live_public_key'];
                        // $otp_gbpReferenceNo = $callback['gbpReferenceNo'];
  
  
                      }

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
                        $this->gbprimepayLogger->addDebug("AfterPlaceDirectOrder error//");
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
                $this->gbprimepayLogger->addDebug("AfterPlaceDirectOrder error//" . $exception->getMessage());
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
