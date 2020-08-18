<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\ConfigInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use GBPrimePay\Payments\Helper\Constant;

class GBPrimePayQrcredit extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'gbprimepay_qrcredit';
    protected $_code = self::CODE;

    protected $_messageManager;
    protected $checkoutSession;
    protected $checkoutRegistry;
    protected $customerSession;
    protected $_config;
    protected $customerFactory;
    protected $cardFactory;
    protected $purchaseFactory;
    protected $gbprimepayLogger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $checkoutRegistry,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \GBPrimePay\Payments\Model\CustomerFactory $customerFactory,
        \GBPrimePay\Payments\Model\CardFactory $cardFactory,
        \GBPrimePay\Payments\Model\PurchaseFactory $purchaseFactory,
        \GBPrimePay\Payments\Logger\Logger $gbprimepayLogger,
        $data = []
    ) {

        parent::__construct(
            $context,
            $checkoutRegistry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
        $this->gbprimepayLogger = $gbprimepayLogger;
        $this->_config = $configHelper;
        $this->cardFactory = $cardFactory;
        $this->purchaseFactory = $purchaseFactory;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->backendAuthSession = $backendAuthSession;
        $this->sessionQuote = $sessionQuote;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutRegistry = $checkoutRegistry;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->_messageManager = $messageManager;
        $this->checkoutData = $checkoutData;
        $this->storeManager = $storeManager;
    }

    protected $_isGateway = true;
    protected $_canAuthorize = false;
    protected $_canOrder = false;
    protected $_canCapture = true;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_isInitializeNeeded = true;

    public function validate()
    {
        return true;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $infoInstance = $this->getInfoInstance();
        $_tmpData = $data->_data;
        $additionalDataRef = $_tmpData['additional_data'];
        $transaction_id = isset($additionalDataRef['transaction_id']) ? $additionalDataRef['transaction_id'] : "";
        $transaction_form = isset($additionalDataRef['transaction_form']) ? $additionalDataRef['transaction_form'] : "";
        $infoInstance->setAdditionalInformation('transaction_id', $transaction_id);
        $infoInstance->setAdditionalInformation('transaction_form', $transaction_form);
        return $this;
    }

    public function _assignData($post)
    {
        return $this;
    }

    public function getConfigPaymentAction()
    {
        return $this::ACTION_AUTHORIZE_CAPTURE;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);
        $order->setCustomerNoteNotify(false);
        $stateObject->setState(\Magento\Sales\Model\Order::STATE_NEW);
        $stateObject->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false); 
        $stateObject->setCanSendNewEmailFlag(false); 
        $stateObject->setIsCustomerNotified(false); 
        $stateObject->setIsNotified(false); 
    }

    public function _purchaseData($purchase)
    {
    $filterpurchase = [];
    $customer_id = $purchase['id_customer'];
    $quote_id = $purchase['quoteid'];
    $filterpurchase = $this->purchaseFactory->create()
        ->getCollection()
        ->addFieldToFilter("magento_customer_id", $customer_id)
        ->addFieldToFilter("quoteid", $quote_id)
        ->getData();

    if (count($filterpurchase) > 0) {
        foreach ($filterpurchase as $pur) {
            if ($pur['magento_customer_id'] === $purchase['id_customer']) {
                $id = $pur['id'];
                $purchaseModel = $this->purchaseFactory->create()->load($id);
                $purchaseModel->setmagento_customer_id($purchase['id_customer']);
                $purchaseModel->setpurchase_method($purchase['method']);
                $purchaseModel->setquoteid($purchase['quoteid']);
                $purchaseModel->setstatus($purchase['status']);
                $purchaseModel->save();
            }
        }
    } else {
      $purchaseModel = $this->purchaseFactory->create();
      $purchaseData = [
          'magento_customer_id' => $purchase['id_customer'],
          'purchase_method' => $purchase['method'],
          'quoteid' => $purchase['quoteid'],
          'status' => $purchase['status']
      ];
      $purchaseModel->setData($purchaseData);
      $purchaseModel->save();
    }
    return $this;
    }

    public function _purchaseDataInactive($purchase)
    {
    $filterpurchase = [];
    $customer_id = $purchase['id_customer'];
    $quote_id = $purchase['quoteid'];
    $filterpurchase = $this->purchaseFactory->create()
        ->getCollection()
        ->addFieldToFilter("magento_customer_id", $customer_id)
        ->addFieldToFilter("quoteid", $quote_id)
        ->getData();

    if (count($filterpurchase) > 0) {
        foreach ($filterpurchase as $pur) {
            if ($pur['magento_customer_id'] === $purchase['id_customer']) {
                $id = $pur['id'];
                $purchaseModel = $this->purchaseFactory->create()->load($id);
                $purchaseModel->setpurchase_method($purchase['method']);
                $purchaseModel->setstatus($purchase['status']);
                $purchaseModel->save();
            }
        }
    }
    return $this;
    }
}
