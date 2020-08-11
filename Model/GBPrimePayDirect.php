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

class GBPrimePayDirect extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'gbprimepay_direct';
    protected $_code = self::CODE;

    protected $_isGateway = true;
    protected $_isOffline = false;
    protected $_canOrder = false;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canFetchTransactionInfo = false;
    protected $_canReviewPayment = false;
    protected $_isInitializeNeeded = true;

    protected $_messageManager;
    protected $checkoutSession;
    protected $customerSession;
    protected $_config;
    protected $customerFactory;
    protected $cardFactory;
    protected $checkoutRegistry;
    protected $purchaseFactory;
    protected $gbprimepayLogger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $checkoutRegistry,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \GBPrimePay\Payments\Model\CustomerFactory $customerFactory,
        \GBPrimePay\Payments\Model\CardFactory $cardFactory,
        \GBPrimePay\Payments\Model\PurchaseFactory $purchaseFactory,
        \GBPrimePay\Payments\Logger\Logger $gbprimepayLogger,
        array $data = []
    ) {
        $this->gbprimepayLogger = $gbprimepayLogger;
        $this->_config = $configHelper;
        $this->cardFactory = $cardFactory;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->backendAuthSession = $backendAuthSession;
        $this->sessionQuote = $sessionQuote;
        $this->checkoutRegistry = $checkoutRegistry;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->checkoutData = $checkoutData;
        $this->purchaseFactory = $purchaseFactory;
        $this->storeManager = $storeManager;

        parent::__construct(
            $context,
            $checkoutRegistry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );
    }
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return AbstractMethod::isAvailable($quote);
    }
    public function validate()
    {
        return AbstractMethod::validate();
    }
    public function initialize($paymentAction, $stateObject)
    {
        try {
            $payment = $this->getInfoInstance();
            $order = $payment->getOrder();
            $order->setCanSendNewEmailFlag(false);
            $order->setCustomerNoteNotify(false);
            $amount = $order->getBaseGrandTotal();
            $order = $payment->getOrder();
            $customer_id = $order->getCustomerId();
            $customerModel = $this->customerFactory->create()
                ->getCollection()
                ->addFieldToFilter("magento_customer_id", $customer_id)
                ->getData();
            if (empty($customerModel)) {
                $customer = $this->_createCustomer($payment);
                $gbprimepayCustomerId = $customer['id'];
            } else {
                $gbprimepayCustomerId = $customerModel[0]['gbprimepay_customer_id'];
            }
            $payment->setAdditionalInformation("gbprimepay_customer_id", $gbprimepayCustomerId);

            $tokenid = $payment->getAdditionalInformation('tokenid');
            if ($tokenid && $tokenid !== "0") {
                $cardModel = $this->cardFactory->create()
                    ->getCollection()
                    ->addFieldToFilter("id", $tokenid)
                    ->getFirstItem();
                $gbprimepayCardId = $cardModel->getData("tokenid");
                $payment->setAdditionalInformation("gbprimepayCardId", $gbprimepayCardId);
                $card = $this->_loadCard($payment, $gbprimepayCardId);
                $gbprimepayCardId = $card['id'];
                $payment->setAdditionalInformation("gbprimepayCardId", $gbprimepayCardId);
            } else {
                $card = $this->_createCard($payment, $gbprimepayCustomerId);
                $gbprimepayCardId = $card['id'];
                $payment->setAdditionalInformation("gbprimepayCardId", $gbprimepayCardId);
            }
            $stateObject->setState(\Magento\Sales\Model\Order::STATE_NEW);
            $stateObject->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $stateObject->setIsNotified(false); 
            $stateObject->setCanSendNewEmailFlag(false); 
            $stateObject->setIsCustomerNotified(false); 
            $stateObject->setIsNotified(false); 
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $infoInstance = $this->getInfoInstance();
        $_tmpData = $data->_data;
        $additionalDataRef = $_tmpData['additional_data'];
        $tokenid = isset($additionalDataRef['tokenid']) ? $additionalDataRef['tokenid'] : "";
        $isSave = isset($additionalDataRef['isSave']) ? $additionalDataRef['isSave'] : "";
        $ccnb = isset($additionalDataRef['cc_number']) ? $additionalDataRef['cc_number'] : "";
        $transaction_id = isset($additionalDataRef['transaction_id']) ? $additionalDataRef['transaction_id'] : "";
        $infoInstance->setAdditionalInformation('data', json_encode($additionalDataRef));
        $infoInstance->setAdditionalInformation('isSave', $isSave);
        $infoInstance->setAdditionalInformation('tokenid', $tokenid);
        $infoInstance->setAdditionalInformation('transaction_id', $transaction_id);
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
            $customer_email = $order->getCustomerEmail();
            $customers = $this->customerFactory->create()
                ->getCollection()
                ->addFieldToFilter("customer_email", $customer_email)
                ->getData();

            if (count($customers) > 0) {
                foreach ($customers as $cus) {
                    if ($cus['email'] === $order->getCustomerEmail()) {
                        $customer = $cus;
                    }
                }
            } else {
                $customer = [
                    "id" => str_replace('.', '', $order->getCustomerEmail() . '-' . $order->getCustomerId() . '-' . time()),
                    "email" => $order->getCustomerEmail(),
                    "country" => $this->_config->convertCountryCodeToIso3($order->getBillingAddress()->getCountryId()),
                    "mobile" => $order->getBillingAddress()->getTelephone(),
                    "address_line1" => (count($order->getBillingAddress()->getStreet())>0) ? $order->getBillingAddress()->getStreet()[0] : '',
                    "address_line2" => (count($order->getBillingAddress()->getStreet())>1) ? $order->getBillingAddress()->getStreet()[1] : '',
                    "state" => $order->getBillingAddress()->getRegion(),
                    "city" => $order->getBillingAddress()->getCity(),
                    "zip" => $order->getBillingAddress()->getPostcode(),
                    "first_name" => $order->getBillingAddress()->getFirstname(),
                    "last_name" => $order->getBillingAddress()->getLastname()
                ];
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
                $this->gbprimepayLogger->addDebug("item //" . $exception->getMessage());
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
    public function _createCard($payment, $customerId)
    {

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $payment->getOrder();

        try {
            $additionalDataRef = $payment->getAdditionalInformation('data');
            $additionalDataRef = json_decode($additionalDataRef);
            $payment->setAdditionalInformation('data', null);


//gettoken





  if ($this->_config->getEnvironment() === 'prelive') {
      $url = Constant::URL_API_TEST;
  } else {
      $url = Constant::URL_API_LIVE;
  }

  $customer_full_name = $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname();

  if($additionalDataRef->is_save=='0'){
    $customer_rememberCard='false';
  }else{
    $customer_rememberCard='true';
  }






  if(isset($additionalDataRef->cc_exp_year)){
      $customer_cc_exp_year = substr($additionalDataRef->cc_exp_year, -2);
  }


  if(isset($additionalDataRef->cc_exp_month)){
    if(strlen($additionalDataRef->cc_exp_month)==1){
      $customer_cc_exp_month='0'.$additionalDataRef->cc_exp_month;
    }else{
      $customer_cc_exp_month=$additionalDataRef->cc_exp_month;
    }
  }





$iniactive = 0;
  if((isset($additionalDataRef->cc_exp_month)) && (isset($additionalDataRef->cc_exp_year))){





  $field = "{\r\n\"rememberCard\": $customer_rememberCard,\r\n\"card\": {\r\n\"number\": \"$additionalDataRef->cc_number\",\r\n\"expirationMonth\": \"$customer_cc_exp_month\",\r\n\"expirationYear\": \"$customer_cc_exp_year\",\r\n\"securityCode\": \"$additionalDataRef->cc_cid\",\r\n\"name\": \"$customer_full_name\"\r\n}\r\n}";







  $callback = $this->_config->sendAPICurl("$url", $field, 'POST');


      if ($callback['resultCode']=="54") {

      }else if ($callback['resultCode']=="02") {

      }else if ($callback['resultCode']=="00") {
            $token_id = $callback['card']['token'];
            $iniactive = 1;
      }
    }

 if($iniactive==1 && !empty($token_id)){

      $currentdate = date('Y-m-d H:i');

      $getgbprimepay_customer_id= $payment->getAdditionalInformation('gbprimepay_customer_id');
      $card = [
          "active" => 1,
          "created_at" => $currentdate,
          "updated_at" => $currentdate,
          "id" => $token_id,
          "id_customer" => $getgbprimepay_customer_id,
          "links" => array(
                          "self" => "/card_accounts/$token_id",
                          "users" => "/card_accounts/$token_id/users"
                      ),
          "card" => $callback['card'],
      ];




            $isLogin = $this->customerSession->isLoggedIn();
            if ($isLogin) {
                $cardModel = $this->cardFactory->create();
                $cardData = [
                    'magento_customer_id' => $payment->getOrder()->getCustomerId(),
                    'tokenid' => $card['id'],
                    'expiry_date' => $card['card']['expirationMonth'] . '/' . $card['card']['expirationYear'],
                    'credit_card_name' => $card['card']['number']
                ];
                $cardModel->setData($cardData);
                $payment->setAdditionalInformation("cardDataSave", $cardData);
            }
            unset($additionalDataRef);


            $payment->setCcLast4(substr($card['card']['number'], -4));
            $payment->setCcExpMonth($card['card']['expirationMonth']);
            $payment->setCcExpYear($card['card']['expirationYear']);

                  }
            return $card;
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("error //" . $exception->getMessage());
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
    public function _loadCard($payment, $customerId)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $payment->getOrder();

        try {
            $additionalDataRef = $payment->getAdditionalInformation('data');
            $additionalDataRef = json_decode($additionalDataRef);
            $payment->setAdditionalInformation('data', null);




$iniactive = 0;

$token_id = $customerId;
$iniactive = 1;

$getgbprimepay_customer_id= $payment->getAdditionalInformation('gbprimepay_customer_id');

 if($iniactive==1 && !empty($token_id)){

      $currentdate = date('Y-m-d H:i');

      $card = [
          "active" => 1,
          "created_at" => $currentdate,
          "updated_at" => $currentdate,
          "id_customer" => $getgbprimepay_customer_id,
          "id" => $token_id,
          "links" => array(
                          "self" => "/card_accounts/$token_id",
                          "users" => "/card_accounts/$token_id/users"
                      ),
      ];



      }
      return $card;

        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("error //" . $exception->getMessage());
            }
            throw new CouldNotSaveException(
                __('Something went wrong. Please try again!')
            );
        }
    }
    

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     */
    public function _secured($payment, $amount)
    {
        try {
$gbprimepayCardId = $payment->getAdditionalInformation('gbprimepayCardId');
$order = $payment->getOrder();

$customer_full_name = $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname();
$callgetMerchantId = $this->_config->getMerchantId();
$callgenerateID = $this->_config->generateID();

$_orderId = $order->getEntityId();
$_incrementId = $order->getIncrementId();
$itemamount = number_format((($amount * 100)/100), 2, '.', '');
$itemdetail = 'Charge for order ' . $_incrementId;
$itemReferenceId = ''.substr(time(), 4, 5).'00'.$_orderId;
$itemcustomerEmail = $order->getCustomerEmail();
$itemmagento_customer_id = $payment->getOrder()->getCustomerId();
$otpCode = 'Y';
$otpResponseUrl = $this->_config->getresponseUrl('response_direct');
$otpBackgroundUrl = $this->_config->getresponseUrl('background_direct');

if ($this->_config->getEnvironment() === 'prelive') {
    $url = Constant::URL_CHARGE_TEST;
} else {
    $url = Constant::URL_CHARGE_LIVE;
}

$field = "{\r\n\"amount\": $itemamount,\r\n\"referenceNo\": \"$itemReferenceId\",\r\n\"detail\": \"$itemdetail\",\r\n\"customerName\": \"$customer_full_name\",\r\n\"customerEmail\": \"$itemcustomerEmail\",\r\n\"merchantDefined1\": \"$callgenerateID\",\r\n\"merchantDefined2\": null,\r\n\"merchantDefined3\": \"$itemReferenceId\",\r\n\"merchantDefined4\": null,\r\n\"merchantDefined5\": null,\r\n\"card\": {\r\n\"token\": \"$gbprimepayCardId\"\r\n},\r\n\"otp\": \"$otpCode\",\r\n\"responseUrl\": \"$otpResponseUrl\",\r\n\"backgroundUrl\": \"$otpBackgroundUrl\"\r\n}\r\n";


$callback = $this->_config->sendCHARGECurl("$url", $field, 'POST');


if ($callback['resultCode']=="00") {
    $isLogin = $this->customerSession->isLoggedIn();
    if ($isLogin) {
        $cardModel = $this->cardFactory->create();
        $getcardDataSave= $payment->getAdditionalInformation('cardDataSave');
        if($getcardDataSave){
            $cardModel->setData($getcardDataSave);
            $cardModel->save();
        }
    }
}
$getgbprimepay_customer_id= $payment->getAdditionalInformation('gbprimepay_customer_id');

$gbpReferenceNo_action = isset($callback['gbpReferenceNo']) ? $callback['gbpReferenceNo'] : '';
    if($gbpReferenceNo_action==true){
      $callbackgbpReferenceNo = $callback['gbpReferenceNo'];
    }else{
      $callbackgbpReferenceNo = '';
    }

$item = array(
    "id" => $callgenerateID,
    "tokenreference" => $gbprimepayCardId,
    "resultCode" => $callback['resultCode'],
    "amount" => $itemamount,
    "referenceNo" => $itemReferenceId,
    "gbpReferenceNo" => $callbackgbpReferenceNo,
    "detail" => $itemdetail,
    "customerName" => $customer_full_name,
    "customerEmail" => $itemcustomerEmail,
    "merchantDefined1" => $callgenerateID,
    "merchantDefined2" => null,
    "merchantDefined3" => $itemReferenceId,
    "merchantDefined4" => null,
    "merchantDefined5" => null,
    "related" => array(
                    "self" => "$getgbprimepay_customer_id",
                    "buyers" => "$callgetMerchantId",
                ),
    "links" => array(
                    "self" => "/charges/$callgenerateID",
                    "buyers" => "/charges/$callgenerateID/buyers",
                    "sellers" => "/charges/$callgenerateID/sellers",
                    "status" => "/charges/$callgenerateID/status",
                    "fees" => "/charges/$callgenerateID/fees",
                    "transactions" => "/charges/$callgenerateID/transactions",
                    "batch_transactions" => "/charges/$callgenerateID/batch_transactions",
                    ),
);

            if ($item['tokenreference']) {
                if ($callback['resultCode'] === '00') {
                    return $item;
                } else {
                    throw new CouldNotSaveException(
                        __('Something went wrong. Please try again!')
                    );
                }
            } else {
                throw new CouldNotSaveException(
                    __('Something went wrong. Please try again!')
                );
            }
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("cap auth //" . $exception->getMessage());
            }

            throw new \Exception(
                $exception->getMessage()
            );
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     */
    public function _capture($payment, $amount)
    {
        try {
$gbprimepayCardId = $payment->getAdditionalInformation('gbprimepayCardId');
$order = $payment->getOrder();

$customer_full_name = $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname();
$callgetMerchantId = $this->_config->getMerchantId();
$callgenerateID = $this->_config->generateID();

$itemamount = number_format((($amount * 100)/100), 2, '.', '');
$itemdetail = 'Charge for order ' . $order->getEntityId();
$itemReferenceId = ''.substr(time(), 4, 5).'00'.$order->getIncrementId();
$itemcustomerEmail = $order->getCustomerEmail();
$itemmagento_customer_id = $payment->getOrder()->getCustomerId();
$otpCode = 'Y';
$otpResponseUrl = $this->_config->getresponseUrl('response_direct');
$otpBackgroundUrl = $this->_config->getresponseUrl('background_direct');

if ($this->_config->getEnvironment() === 'prelive') {
    $url = Constant::URL_CHARGE_TEST;
} else {
    $url = Constant::URL_CHARGE_LIVE;
}

$field = "{\r\n\"amount\": $itemamount,\r\n\"referenceNo\": \"$itemReferenceId\",\r\n\"detail\": \"$itemdetail\",\r\n\"customerName\": \"$customer_full_name\",\r\n\"customerEmail\": \"$itemcustomerEmail\",\r\n\"merchantDefined1\": \"$callgenerateID\",\r\n\"merchantDefined2\": null,\r\n\"merchantDefined3\": \"$itemReferenceId\",\r\n\"merchantDefined4\": null,\r\n\"merchantDefined5\": null,\r\n\"card\": {\r\n\"token\": \"$gbprimepayCardId\"\r\n},\r\n\"otp\": \"$otpCode\",\r\n\"responseUrl\": \"$otpResponseUrl\",\r\n\"backgroundUrl\": \"$otpBackgroundUrl\"\r\n}\r\n";

// if ($this->_config->getCanDebug()) {
//     $this->gbprimepayLogger->addDebug("Debug field //" . print_r($field, true));
// }

$callback = $this->_config->sendCHARGECurl("$url", $field, 'POST');

if ($this->_config->getCanDebug()) {
    $this->gbprimepayLogger->addDebug("Debug sendCHARGECurl callback //" . print_r($callback, true));
}

if ($callback['resultCode']=="00") {
    $isLogin = $this->customerSession->isLoggedIn();
    if ($isLogin) {
        $cardModel = $this->cardFactory->create();
        $getcardDataSave= $payment->getAdditionalInformation('cardDataSave');
        if($getcardDataSave){
            $cardModel->setData($getcardDataSave);
            $cardModel->save();
        }
    }
}
$getgbprimepay_customer_id= $payment->getAdditionalInformation('gbprimepay_customer_id');
$item = array(
    "id" => $callgenerateID,
    "tokenreference" => $gbprimepayCardId,
    "resultCode" => $callback['resultCode'],
    "amount" => $itemamount,
    "referenceNo" => $itemReferenceId,
    "detail" => $itemdetail,
    "customerName" => $customer_full_name,
    "customerEmail" => $itemcustomerEmail,
    "merchantDefined1" => $callgenerateID,
    "merchantDefined2" => null,
    "merchantDefined3" => $itemReferenceId,
    "merchantDefined4" => null,
    "merchantDefined5" => null,
    "related" => array(
                    "self" => "$getgbprimepay_customer_id",
                    "buyers" => "$callgetMerchantId",
                ),
    "links" => array(
                    "self" => "/charges/$callgenerateID",
                    "buyers" => "/charges/$callgenerateID/buyers",
                    "sellers" => "/charges/$callgenerateID/sellers",
                    "status" => "/charges/$callgenerateID/status",
                    "fees" => "/charges/$callgenerateID/fees",
                    "transactions" => "/charges/$callgenerateID/transactions",
                    "batch_transactions" => "/charges/$callgenerateID/batch_transactions",
                    ),
);

            if ($item['tokenreference']) {
                if ($callback['resultCode'] === '00') {
                    return $item;
                } else {
                    throw new CouldNotSaveException(
                        __('Something went wrong. Please try again!')
                    );
                }
            } else {
                throw new CouldNotSaveException(
                    __('Something went wrong. Please try again!')
                );
            }
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("cap auth //" . $exception->getMessage());
            }

            throw new \Exception(
                $exception->getMessage()
            );
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $order = $payment->getOrder();
            $id = $payment->getAdditionalInformation('id');
            $payment->setAdditionalInformation('isCapture', true);
            $magentoOrderId = $order->getIncrementId();
            $this->_messageManager->addSuccessMessage("Your order (ID: $magentoOrderId) was successful!");
            $payment->setParentTransactionId($id);
            $payment->setTransactionId($id);
            $payment->setLastTransId($id);
            $payment->setIsTransactionClosed(0);
            $payment->setShouldCloseParentTransaction(0);
            $order->save();
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("cap //" . $exception->getMessage());
            }

            throw new CouldNotSaveException(
                __('Something went wrong. Please try again!')
            );
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     */
    public function void(
        \Magento\Payment\Model\InfoInterface $payment
    ) {
        throw new \Magento\Framework\Exception\LocalizedException(__('The Void action is not available.'));
    }

    public function cancel(
        \Magento\Payment\Model\InfoInterface $payment
    ) {
        throw new \Magento\Framework\Exception\LocalizedException(__('The Cancel action is not available.'));
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
