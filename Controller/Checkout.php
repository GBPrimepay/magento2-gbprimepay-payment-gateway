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
    protected $resultRedirectFactory;
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
    protected $quoteRepository;
    protected $jsonFactory;
    protected $config;
    protected $storeManager;
    protected $baseUrl;
    protected $_config;
    protected $gbprimepayLogger;
    protected $CsrfValidator;
    protected $gbprimepayDirect;
    protected $gbprimepayInstallment;
    protected $gbprimepayQrcode;
    protected $gbprimepayQrcredit;
    protected $gbprimepayQrwechat;
    protected $gbprimepayBarcode;
    protected $_messageManager;
    protected $orderManagement;
    protected $collectionFactory;
    protected $orderSender;
    protected $formKeyValidator;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $orderPayment,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Payment\Transaction\Builder $transactionBuilder,
        \Magento\Sales\Model\OrderRepository $orderRepository,        
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $checkoutRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \GBPrimePay\Payments\Logger\Logger $gbprimepayLogger,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \GBPrimePay\Payments\Controller\Checkout\CsrfValidator $CsrfValidator,
        \GBPrimePay\Payments\Model\GBPrimePayDirect $gbprimepayDirect,
        \GBPrimePay\Payments\Model\GBPrimePayInstallment $gbprimepayInstallment,
        \GBPrimePay\Payments\Model\GBPrimePayQrcode $gbprimepayQrcode,
        \GBPrimePay\Payments\Model\GBPrimePayQrcredit $gbprimepayQrcredit,
        \GBPrimePay\Payments\Model\GBPrimePayQrwechat $gbprimepayQrwechat,
        \GBPrimePay\Payments\Model\GBPrimePayBarcode $gbprimepayBarcode,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $collectionFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Quote\Api\CartManagementInterface $placeManagement,
        \Magento\Quote\Model\Quote\PaymentFactory $paymentFactory,
        $params = []
    ) {
        $this->_messageManager = $context->getMessageManager();
        $this->CsrfValidator = $CsrfValidator;
        $this->gbprimepayDirect = $gbprimepayDirect;
        $this->gbprimepayInstallment = $gbprimepayInstallment;
        $this->gbprimepayQrcode = $gbprimepayQrcode;
        $this->gbprimepayQrcredit = $gbprimepayQrcredit;
        $this->gbprimepayQrwechat = $gbprimepayQrwechat;
        $this->gbprimepayBarcode = $gbprimepayBarcode;
        $this->gbprimepayLogger = $gbprimepayLogger;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->_config = $configHelper;
        $this->checkoutSession = $checkoutSession;
        $this->PageFactory = $resultPageFactory;
        $this->RedirectFactory = $resultRedirectFactory;
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
        $this->quoteRepository = $quoteRepository;
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
    public function sendEmailCustomer($order)
    {
        try {
            $this->orderSender->send($order);
        } catch (\Exception $e) {
            $this->gbprimepayLogger->critical($e);
        }
    }
    
    public function reloadCustomerId($payment, int $customerId, $transaction_form)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            $this->gbprimepayLogger->critical($e);
        }
        $this->customerSession->setCustomerDataAsLoggedIn($customer);
        if ($this->_config->getCanDebug()) {
            $this->gbprimepayLogger->addDebug("\r\n reload //" . $transaction_form);
        }       
    }

    public function cancelOrder()
    {
        try {
            $orderId = $this->checkoutSession->getLastRealOrder()->getId();
            $this->orderManagement->cancel($orderId);
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }
    public function holdOrder()
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
    public function unHoldOrder()
    {
        try {
            $orderId = $this->checkoutSession->getLastRealOrder()->getId();
            $this->orderManagement->unHold($orderId);
            return $orderId;
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }
    public function placeOrder($quote_id,$paymentMethod)
    {
        try {
            $orderId = $this->placeManagement->placeOrder($quote_id);
            return $orderId;
        } catch (\Exception $e) {
            $this->gbprimepayLogger->addCritical($e->getMessage());
        }
    }
    public function getOrderIdByIncrementId($incrementId)
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
    public function getIncrementIdByOrderId($entityId)
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
    public function setOrderStatePendingStatus($orderId, $status, $order_note)
    {
        $order = $this->getQuoteByOrderId($orderId);
        $order->setCanSendNewEmailFlag(false);
        $order->setState($status);
        $order->setStatus($status);
        $order->addStatusToHistory($status, $order_note, false);
        $order->setIsCustomerNotified(false);
        $this->saveOrder($order);
    }
    public function failureOrder($orderId, $status, $order_note)
    {
        $order = $this->getQuoteByOrderId($orderId);
        $order->setCanSendNewEmailFlag(false);
        $order->setState($status);
        $order->setStatus($status);
        $order->addStatusToHistory($status, $order_note, true);
        $order->setIsCustomerNotified(true);
        $order->registerCancellation('Order canceled by customer')->save();
        $quote = $this->quoteRepository->get($order->getQuoteId());
            if ($quote->getId()){
                $quote = $this->quoteRepository->get($order->getQuoteId());
                $quote->setIsActive(1)->setReservedOrderId(null);
                $this->quoteRepository->save($quote);
            }

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
            if($dataCode['gbp_reference_no']){
                $ResponseTransactionId = $dataCode['gbp_reference_no'];
            }else{
                $ResponseTransactionId = 0;
            }
            return $ResponseTransactionId;
        }else{
            return 0;
        }
    }













}
