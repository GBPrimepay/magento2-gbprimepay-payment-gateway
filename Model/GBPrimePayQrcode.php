<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Model;


use Magento\Framework\Exception\CouldNotSaveException;
use GBPrimePay\Payments\Helper\Constant;

class GBPrimePayQrcode extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'gbprimepay_qrcode';
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
if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("validate start//");
}
        return true;
    }




    public function assignData(\Magento\Framework\DataObject $data)
    {
if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("assignData start//");
$this->gbprimepayLogger->addDebug("assignData data//" . print_r($data, true));
}

        $infoInstance = $this->getInfoInstance();
        $_tmpData = $data->_data;
        $additionalDataRef = $_tmpData['additional_data'];
        $infoInstance->setAdditionalInformation('data', json_encode($additionalDataRef));









    }

    public function getConfigPaymentAction()
    {
        return $this::ACTION_AUTHORIZE_CAPTURE;
    }

    public function initialize($paymentAction, $stateObject)
    {
        try {
if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("initialize start//");
}
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

            $qrcode = $this->_createQrcode($payment, $gbprimepayCustomerId);
            $gbprimepayQrcodeId = $qrcode['resultDisplayCode'];

            if ($this->_config->getCanDebug()) {
            $this->gbprimepayLogger->addDebug("resultDisplayCode qrcode//" . print_r($qrcode, true));
            }

            $payment->setAdditionalInformation("gbprimepayQrcodeId", $gbprimepayQrcodeId);
            // $payment->setAdditionalInformation("gbprimepayQrcodeData", $qrcode['qrcode']);
            $payment->setAdditionalInformation("gbprimepayQrcodeData", $qrcode['resultDisplayCode']);

$waitforcapture = $this->_waitforcapture($order->getPayment(), $order->getBaseGrandTotal());


            // del
                          // $qrcode = MagentoPay::QrcodeAccount()->create([
                          //     "user_id" => $customerId,
                          //     "qrcode_name" => $additionalDataRef['qrcode_name'],
                          //     "account_name" => $additionalDataRef['qrcode_account_name'],
                          //     "routing_number" => $additionalDataRef['qrcode_routing_number'],
                          //     "account_number" => $additionalDataRef['qrcode_account_number'],
                          //     "account_type" => $additionalDataRef['account_type'],
                          //     "holder_type" => $additionalDataRef['holder_type'],
                          //     "country" => $additionalDataRef['qrcode_country'],
                          // ]);
            // del

            // $item = $this->_debitAuthority($payment, $amount, $gbprimepayQrcodeId);






            // if ($this->_config->getCanDebug()) {
            // $this->gbprimepayLogger->addDebug("initialize item//" . print_r($item, true));
            // }



            //
            //
            // if ($item['id'] && $item['state'] === 'approved') {
            //     $capture = $this->_capture($payment, $amount);
            //     if ($capture['state'] == 'completed') {
            //         $stateObject->setData('state', \Magento\Sales\Model\Order::STATE_PROCESSING);
            //         $payment->setAdditionalInformation("back_transaction_id", $capture['id']);
            //         $totalDue = $order->getTotalDue();
            //         $baseTotalDue = $order->getBaseTotalDue();
            //         $payment->setAmountAuthorized($totalDue);
            //         $payment->setBaseAmountAuthorized($baseTotalDue);
            //         $payment->capture(null);
            //
            //         return $this;
            //     } else {
            //         //pending payment, waiting for callback
            //         $order->setCanSendNewEmailFlag(false);
            //     }
            // } else {
            //     if ($this->_config->getCanDebug()) {
            //         $this->gbprimepayLogger->addDebug("autho 2//");
            //     }
            //     throw new CouldNotSaveException(
            //         __('Something went wrong. Please try again!')
            //     );
            // }
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
if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("capture start//");
}
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
    public function _debitAuthority($payment, $amount, $qrcodeId)
    {
        try {
if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("_debitAuthority start//");
}
            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $order = $payment->getOrder();
            $orderId = $order->getIncrementId();

            $field = [
                "account_id" => $qrcodeId,
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
if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("_createCustomer start//");
}
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
    public function _createQrcode($payment, $customerId)
    {
if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("_createQrcode start//");
}
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $payment->getOrder();
        $amount = $order->getBaseGrandTotal();

        try {
            $additionalDataRef = $payment->getAdditionalInformation('data');
            $additionalDataRef = json_decode($additionalDataRef, true);
            $payment->setAdditionalInformation('data', null);

//genqrcode





              if ($this->_config->getEnvironment() === 'prelive') {
                  $url = Constant::URL_QRCODE_TEST;
                  $itemtoken = $this->_config->getTestTokenKey();
              } else {
                  $url = Constant::URL_QRCODE_LIVE;
                  $itemtoken = $this->_config->getLiveTokenKey();
              }

              $customer_full_name = $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname();

$itemamount = number_format((($amount * 100)/100), 2, '.', '');
$itemreferenceno = $order->getIncrementId();
$itemresponseurl = $this->_config->getresponseUrl('response_qrcode');
$itembackgroundurl = $this->_config->getresponseUrl('background_qrcode');
$itemcustomerEmail = $order->getCustomerEmail();
$callgetMerchantId = $this->_config->getMerchantId();
$callgenerateID = $this->_config->generateID();











            $iniactive = 0;






              $field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\n$itemtoken\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$itemamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$itemreferenceno\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"payType\"\r\n\r\nF\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"responseUrl\"\r\n\r\n$itemresponseurl\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"backgroundUrl\"\r\n\r\n$itembackgroundurl\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"detail\"\r\n\r\n$callgetMerchantId\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerName\"\r\n\r\n$customer_full_name\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerEmail\"\r\n\r\n$itemcustomerEmail\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined1\"\r\n\r\n$callgenerateID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined2\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined3\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined4\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined5\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";




                // if ($this->_config->getCanDebug()) {
                //     $this->gbprimepayLogger->addDebug("field//" . print_r($field, true));
                // }








              $callback = $this->_config->sendQRCurl("$url", $field, 'POST');

              // if ($this->_config->getCanDebug()) {
              //     $this->gbprimepayLogger->addDebug("Debug 2 callback//" . print_r($callback, true));
              // }

                  if ($callback=="Incomplete information") {
                    $qrcode = [
                        "resultDisplayCode" => "",
                    ];

                  }else{
                        // echo $callback;
                        // $this->_registry->register('generateqrcode', $callback);

                        $this->checkoutSession->setGenerateQrcode($callback);
                        $this->checkoutSession->setGenerateQrcodeStatus('true');
                        // $this->checkoutSession->getGenerateQrcode();
                        // if ($this->_config->getCanDebug()) {
                        //     $this->gbprimepayLogger->addDebug("Debug callback//" . print_r($callback, true));
                        // }
                        $iniactive = 1;
                        $qrcode = [
                            "resultDisplayCode" => "$itemreferenceno",
                        ];
                  }




                        // unset($additionalDataRef);


            return $qrcode;
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("qrcode//" . $exception->getMessage());
            }
            throw new CouldNotSaveException(
                __('Something went wrong. Please try again!')
            );
        }
    }











    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     */
    public function _capture($amount, $_payment)
    {
        try {
if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("_capture start//");
}



// $orderId = $this->checkoutSession->getLastRealOrder()->getId();
// $payment = $order->getPayment();
// $order = $payment->getOrder();







            $getgbprimepay_customer_id = $_payment['merchantDefined2'];
            $callgenerateID = $_payment['merchantDefined1'];
            $callgetMerchantId = $_payment['gbpReferenceNo'];
            $itemamount = $_payment['amount'];
            $itemdetail = $_payment['detail'];
            $customer_full_name = $_payment['customerName'];
            $itemcustomerEmail = $_payment['customerEmail'];

            $item = array(
                "id" => $callgenerateID,
                "resultCode" => $_payment['resultCode'],
                "amount" => $itemamount,
                "referenceNo" => $callgetMerchantId,
                "detail" => $callgetMerchantId,
                "customerName" => $customer_full_name,
                "customerEmail" => $itemcustomerEmail,
                "merchantDefined1" => $callgenerateID,
                "merchantDefined2" => null,
                "merchantDefined3" => null,
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

            if ($callgetMerchantId) {
                if ($_payment['resultCode'] === '00') {
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

            throw new CouldNotSaveException(
                __('Something went wrong. Please try again!')
            );
        }
    }







    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     */
    public function _waitforcapture($payment, $amount)
    {
        try {
    if ($this->_config->getCanDebug()) {
    $this->gbprimepayLogger->addDebug("_waitforcapture start//");
    }



        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("_waitforcapture error //" . $exception->getMessage());
            }

        }
    }








}
