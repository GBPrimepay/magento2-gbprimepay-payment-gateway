<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
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
    protected $checkoutRegistry;
    protected $resultJsonFactory;
    protected $resultPageFactory;
    protected $orderFactory;
    protected $quoteFactory;
    protected $placeManagement;
    protected $paymentFactory;
    protected $orderPayment;
    protected $invoiceCollectionFactory;
    protected $invoiceService;
    protected $transactionFactory;
    protected $transactionBuilder;
    protected $orderRepository;
    protected $jsonFactory;
    protected $config;
    protected $storeManager;
    protected $baseUrl;
    protected $_config;
    protected $gbprimepayLogger;
    protected $gbprimepayDirect;
    protected $gbprimepayInstallment;
    protected $gbprimepayQrcode;
    protected $gbprimepayQrcredit;
    protected $gbprimepayBarcode;
    protected $_messageManager;
    protected $orderManagement;
    protected $collectionFactory;
    protected $orderSender;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $orderPayment,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Payment\Transaction\Builder $transactionBuilder,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $checkoutRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \GBPrimePay\Payments\Logger\Logger $gbprimepayLogger,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \GBPrimePay\Payments\Model\GBPrimePayDirect $gbprimepayDirect,
        \GBPrimePay\Payments\Model\GBPrimePayInstallment $gbprimepayInstallment,
        \GBPrimePay\Payments\Model\GBPrimePayQrcode $gbprimepayQrcode,
        \GBPrimePay\Payments\Model\GBPrimePayQrcredit $gbprimepayQrcredit,
        \GBPrimePay\Payments\Model\GBPrimePayBarcode $gbprimepayBarcode,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $collectionFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Quote\Api\CartManagementInterface $placeManagement,
        \Magento\Quote\Model\Quote\PaymentFactory $paymentFactory,
        $params = []
    ) {
        $this->_messageManager = $context->getMessageManager();
        $this->gbprimepayDirect = $gbprimepayDirect;
        $this->gbprimepayInstallment = $gbprimepayInstallment;
        $this->gbprimepayQrcode = $gbprimepayQrcode;
        $this->gbprimepayQrcredit = $gbprimepayQrcredit;
        $this->gbprimepayBarcode = $gbprimepayBarcode;
        $this->gbprimepayLogger = $gbprimepayLogger;
        $this->customerSession = $customerSession;
        $this->_config = $configHelper;
        $this->checkoutSession = $checkoutSession;
        $this->PageFactory = $resultPageFactory;
        $this->checkoutRegistry = $checkoutRegistry;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->placeManagement = $placeManagement;
        $this->paymentFactory = $paymentFactory;
        $this->orderPayment = $orderPayment;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->transactionBuilder = $transactionBuilder;        
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->jsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
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
    protected function holdOrder()
    {
        try {
            $orderId = $this->checkoutSession->getLastRealOrder()->getId();
            $orderIncrementId = $this->checkoutSession->getLastRealOrder()->getIncrementId();
            $this->placeManagement->hold($orderId);
            return $orderIncrementId;
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }
    protected function unHoldOrder()
    {
        try {
            $orderId = $this->checkoutSession->getLastRealOrder()->getId();
            $this->orderManagement->unHold($orderId);
            return $orderId;
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }
    protected function placeOrder($quote_id,$paymentMethod)
    {
        try {
            $orderId = $this->placeManagement->placeOrder($quote_id);
            return $orderId;
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }
    protected function getOrderIdByIncrementId($incrementId)
    {
        try {
            $orderModel = $this->orderFactory->create();
            $order = $orderModel->loadByIncrementId($incrementId);
            $orderId = $order->getId();
            return $orderId;
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }
    protected function getIncrementIdByOrderId($entityId)
    {
        try {
            $orderModel = $this->orderFactory->create();
            $order = $orderModel->loadByAttribute('entity_id',$entityId);
            $orderId = $order->getIncrementId();
            return $orderId;
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }


    public function getQuoteByOrderId($orderId) {        
        return $this->orderPayment->loadByIncrementId($orderId);
    }

    public function generateInvoice($orderId, $payment_type){
        try {
            $order = $this->getQuoteByOrderId($orderId);
            $invoices = $this->invoiceCollectionFactory->create()
            ->addAttributeToFilter('order_id', array('eq' => $order->getId()));
            $invoices->getSelect()->limit(1);
            if ((int)$invoices->count() !== 0) {
                return null;
            }
            if(!$order->canInvoice()) {
                return null;
            }
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
        return $invoice;
    }
    public function generateTransaction($orderId, $transaction_id, $_gbpReferenceNum){
        try {
            $order = $this->getQuoteByOrderId($orderId);
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $payment = $order->getPayment();
            $dataCode = $payment->getAdditionalInformation();
            $payment->setAdditionalInformation("transaction_id", $transaction_id);
            $payment->setAdditionalInformation("gbp_reference_no", $_gbpReferenceNum);
            // $payment->setAdditionalInformation('method_title', $dataCode['method_title']);
            $payment->setLastTransId($transaction_id);
            $payment->setIsTransactionClosed(0);
            $payment->setShouldCloseParentTransaction(0);
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                            $order->getGrandTotal()
                        );

            $message = __('Authorized amount is %1.', $formatedPrice);
            $trans = $this->transactionBuilder;
            $transaction = $trans->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($transaction_id)
            ->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$payment->getAdditionalInformation()]
            )
            ->setFailSafe(true)
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId($transaction_id);

            $payment->save();
            $order->save();
            $transaction->save();


        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }
    public function setOrderStateAndStatus($orderId, $status, $order_note)
    {
        $order = $this->getQuoteByOrderId($orderId);
        $order->setCanSendNewEmailFlag(true);
        $this->sendEmailCustomer($order);
        $order->setState($status);
        $order->setStatus($status);
        $order->addStatusToHistory($status, $order_note, true);
        $order->setIsCustomerNotified(true);
        $this->saveOrder($order);
    }
    public function saveOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        try {
           $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }
    public function getOrderCompleteStatus($orderId)
    {
        $order = $this->getQuoteByOrderId($orderId);
        $payment = $order->getPayment();
        $dataCode = $payment->getAdditionalInformation();
        $ResponseState = $order->getState();
        $ResponseStatus = $order->getStatus();
        if(($ResponseState == "processing") && ($ResponseStatus == "processing")){
            $ResponseTransactionId = isset($dataCode['gbp_reference_no']) ? $dataCode['gbp_reference_no'] : '';
            return $ResponseTransactionId;
        }else{
            return 0;
        }
    }













}
