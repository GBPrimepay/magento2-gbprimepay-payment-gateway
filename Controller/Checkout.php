<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller;

abstract class Checkout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    protected $resultJsonFactory;
    protected $orderFactory;
    protected $jsonFactory;
    protected $config;
    protected $storeManager;
    protected $baseUrl;
    protected $_config;
    protected $gbprimepayLogger;
    protected $gbprimepayDirect;
    protected $gbprimepayQrcode;
    protected $gbprimepayBarcode;
    protected $_messageManager;
    protected $orderManagement;
    protected $orderSender;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \GBPrimePay\Payments\Logger\Logger $gbprimepayLogger,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \GBPrimePay\Payments\Model\GBPrimePayDirect $gbprimepayDirect,
        \GBPrimePay\Payments\Model\GBPrimePayQrcode $gbprimepayQrcode,
        \GBPrimePay\Payments\Model\GBPrimePayBarcode $gbprimepayBarcode,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        $params = []
    ) {
        $this->_messageManager = $context->getMessageManager();
        $this->gbprimepayDirect = $gbprimepayDirect;
        $this->gbprimepayQrcode = $gbprimepayQrcode;
        $this->gbprimepayBarcode = $gbprimepayBarcode;
        $this->gbprimepayLogger = $gbprimepayLogger;
        $this->customerSession = $customerSession;
        $this->_config = $configHelper;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->jsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->orderManagement = $orderManagement;
        parent::__construct($context);
        $this->baseUrl = $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    protected function sendEmailCustomer($order)
    {
        try {
            $this->orderSender->send($order);
        } catch (\Exception $e) {
            $this->gbprimepayLogger->critical($e);
        }
    }

    protected function cancelOrder()
    {
        try {
            $orderId = $this->checkoutSession->getLastRealOrder()->getId();
            $this->orderManagement->cancel($orderId);
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }






}
