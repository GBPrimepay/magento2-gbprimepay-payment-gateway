<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Model;

use MagentoPay\MagentoPay;
use Magento\Framework\Exception\CouldNotSaveException;
use GBPrimePay\Payments\Helper\Constant;

class GBPrimePayBarcode extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'gbprimepay_barcode';
    protected $_code = self::CODE;

    protected $_messageManager;
    protected $checkoutSession;
    protected $customerSession;
    protected $_config;
    protected $customerFactory;
    protected $cardFactory;
    protected $gbprimepayLogger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \GBPrimePay\Payments\Model\CustomerFactory $customerFactory,
        \GBPrimePay\Payments\Model\CardFactory $cardFactory,
        \GBPrimePay\Payments\Logger\Logger $gbprimepayLogger,
        $data = []
    ) {

        parent::__construct(
            $context,
            $registry,
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
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->backendAuthSession = $backendAuthSession;
        $this->sessionQuote = $sessionQuote;
        $this->checkoutSession = $checkoutSession;
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
        $infoInstance->setAdditionalInformation('data', json_encode($additionalDataRef));
    }

    public function _gbprimepayInit()
    {
        try {
            $env = $this->_config->getEnvironment();
            $login = $this->_config->getApiLogin();
            $pass = $this->_config->getApiPassword();
            MagentoPay::Configuration()->environment($env);
            MagentoPay::Configuration()->login($login);
            MagentoPay::Configuration()->password($pass);
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("error gbprimepay init//" . $exception->getMessage());
            }
        }
    }

    public function getConfigPaymentAction()
    {
        return $this::ACTION_AUTHORIZE_CAPTURE;
    }

    public function initialize($paymentAction, $stateObject)
    {
        try {
            $this->_gbprimepayInit();
            /**
             * @var \Magento\Sales\Model\Order $order
             */
            /** @var \Magento\Sales\Model\Order\Payment $payment $payment */
            $payment = $this->getInfoInstance();
            $order = $payment->getOrder();
            $amount = $order->getBaseGrandTotal();
            $customer_id = $order->getCustomerId();
            $customerModel = $this->customerFactory->create()
                ->getCollection()
                ->addFieldToFilter("magento_customer_id", $customer_id)
                ->getData();
            if (count($customerModel) === 0) {
                $customer = $this->_createCustomer($payment);
                $gbprimepayCustomerId = $customer['id'];
            } else {
                $gbprimepayCustomerId = $customerModel[0]['gbprimepay_customer_id'];
            }

            $barcode = $this->_createBarcode($payment, $gbprimepayCustomerId);
            $gbprimepayBarcodeId = $barcode['id'];
            $payment->setAdditionalInformation("gbprimepayBarcodeId", $gbprimepayBarcodeId);
            $payment->setAdditionalInformation("gbprimepayBarcodeData", $barcode['barcode']);

            $item = $this->_debitAuthority($payment, $amount, $gbprimepayBarcodeId);
            if ($item['id'] && $item['state'] === 'approved') {
                $capture = $this->_capture($payment, $amount);
                if ($capture['state'] == 'completed') {
                    $stateObject->setData('state', \Magento\Sales\Model\Order::STATE_PROCESSING);
                    $payment->setAdditionalInformation("back_transaction_id", $capture['id']);
                    $totalDue = $order->getTotalDue();
                    $baseTotalDue = $order->getBaseTotalDue();
                    $payment->setAmountAuthorized($totalDue);
                    $payment->setBaseAmountAuthorized($baseTotalDue);
                    $payment->capture(null);

                    return $this;
                } else {
                    //pending payment, waiting for callback
                    $order->setCanSendNewEmailFlag(false);
                }
            } else {
                if ($this->_config->getCanDebug()) {
                    $this->gbprimepayLogger->addDebug("autho 2//");
                }
                throw new CouldNotSaveException(
                    __('Something went wrong. Please try again!')
                );
            }
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("autho 3//" . $exception->getMessage());
            }
            throw new CouldNotSaveException(
                __('Something went wrong. Please try again!')
            );
        }

        return parent::initialize($paymentAction, $stateObject); // TODO: Change the autogenerated stub
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $payment->setAdditionalInformation('isCapture', true);
        $order = $payment->getOrder();
        $magentoOrderId = $order->getIncrementId();
        $quoteId = $order->getQuoteId();
        $this->_messageManager->addSuccessMessage("Your order (ID: $magentoOrderId) was successful!");
        $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);
        $transactionId = $payment->getAdditionalInformation("back_transaction_id");
        $payment->setParentTransactionId($transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setIsTransactionClosed(0);
        $payment->setShouldCloseParentTransaction(0);
        $order->save();
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param $amount
     */
    public function _debitAuthority($payment, $amount, $barcodeId)
    {
        try {
            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $order = $payment->getOrder();
            $orderId = $order->getIncrementId();

            $field = [
                "account_id" => $barcodeId,
                "amount" => $amount * 100,

            ];
            if ($this->_config->getEnvironment() === 'prelive') {
                $url = Constant::URL_DEBIT_AUTHORITY_TEST;
            } else {
                $url = Constant::URL_DEBIT_AUTHORITY;
            }
            $response = $this->_config->sendCurl($url, $field, 'POST');
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("debit authority//" . print_r($response, true));
            }

            return $response['direct_debit_authorities'];
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("debit authority//" . $exception->getMessage());
            }
            throw new CouldNotSaveException(
                __('Something went wrong. Please try again!')
            );
        }
    }

    /**
     * @param $payment
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function _createCustomer($payment)
    {
        try {
            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $order = $payment->getOrder();

            $customer = [];
            $customers = MagentoPay::User()->getList(array(
                'search' => $order->getCustomerEmail()
            ));

            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("customer//" . print_r($customers, true));
            }

            if (count($customers) > 0) {
                foreach ($customers as $cus) {
                    if ($cus['email'] === $order->getCustomerEmail()) {
                        $customer = $cus;
                    }
                }
            } else {
                $customer = MagentoPay::User()->create([
                    "id" => str_replace('.', '', $order->getCustomerEmail() . '-' . $order->getCustomerId() . '-' . time()),
                    "email" => $order->getCustomerEmail(),
                    "country" => $this->_config->convertCountryCodeToIso3($order->getBillingAddress()->getCountryId()),
                    "mobile" => $order->getBillingAddress()->getTelephone(),
                    "address_line1" => (count($order->getBillingAddress()->getStreet()) > 0) ? $order->getBillingAddress()->getStreet()[0] : '',
                    "address_line2" => (count($order->getBillingAddress()->getStreet()) > 1) ? $order->getBillingAddress()->getStreet()[1] : '',
                    "state" => $order->getBillingAddress()->getRegion(),
                    "city" => $order->getBillingAddress()->getCity(),
                    "zip" => $order->getBillingAddress()->getPostcode(),
                    "first_name" => $order->getBillingAddress()->getFirstname(),
                    "last_name" => $order->getBillingAddress()->getLastname()
                ]);
            }

            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("customer//" . print_r($customer, true));
            }

            $isLogin = $this->customerSession->isLoggedIn();
            if ($isLogin) {
                $data = [
                    'magento_customer_id' => $this->customerSession->getCustomerId(),
                    'gbprimepay_customer_id' => $customer['id'],
                    'customer_email' => $customer['email']
                ];
                $customerModel = $this->customerFactory->create();
                $customerModel->setData($data);
                $customerModel->save();
            }

            return $customer;
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("item//" . $exception->getMessage());
            }
            throw new CouldNotSaveException(
                __('Something went wrong. Please try again!')
            );
        }
    }


    /**
     * @param $payment
     * @param $customerId
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function _createBarcode($payment, $customerId)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $payment->getOrder();

        try {
            $additionalDataRef = $payment->getAdditionalInformation('data');
            $additionalDataRef = json_decode($additionalDataRef, true);
            $payment->setAdditionalInformation('data', null);
            $barcode = MagentoPay::BarcodeAccount()->create([
                "user_id" => $customerId,
                "barcode_name" => $additionalDataRef['barcode_name'],
                "account_name" => $additionalDataRef['barcode_account_name'],
                "routing_number" => $additionalDataRef['barcode_routing_number'],
                "account_number" => $additionalDataRef['barcode_account_number'],
                "account_type" => $additionalDataRef['account_type'],
                "holder_type" => $additionalDataRef['holder_type'],
                "country" => $additionalDataRef['barcode_country'],
            ]);

            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("barcode//" . print_r($barcode, true));
            }

            if ($barcode['id']) {
                $barcode = MagentoPay::BarcodeAccount()->get($barcode['id']);
            }
            unset($additionalDataRef);

            return $barcode;
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("barcode//" . $exception->getMessage());
            }
            throw new CouldNotSaveException(
                __('Something went wrong. Please try again!')
            );
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     */
    public function _capture($payment, $amount)
    {
        try {
            $gbprimepayBarcodeId = $payment->getAdditionalInformation('gbprimepayBarcodeId');
            $order = $payment->getOrder();
            $item = MagentoPay::Charges()->create(array(
                "account_id" => $gbprimepayBarcodeId,
                "amount" => $amount * 100,
                "email" => $order->getCustomerEmail(),
                "zip" => $order->getBillingAddress()->getPostcode(),
                "country" => $this->_config->convertCountryCodeToIso3($order->getBillingAddress()->getCountryId()),
                "currency" => $order->getBaseCurrencyCode(),
                "retain_account" => false,
                "custom_descriptor" => $order->getIncrementId()
            ));

            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("cap //" . print_r($item, true));
            }
            if ($item['id']) {
//                if ($item['state'] === 'completed' || $item['state'] === 'payment_pending') {
//                    return $item;
//                } else {
//                    throw new CouldNotSaveException(
//                        __('Something went wrong. Please try again!')
//                    );
//                }
                return $item;
            } else {
                throw new CouldNotSaveException(
                    __('Something went wrong. Please try again!')
                );
            }
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("cap auth //" . $exception->getMessage());
            }

            throw new CouldNotSaveException(
                __('Something went wrong. Please try again!')
            );
        }
    }
}
