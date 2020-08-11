<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use GBPrimePay\Payments\Helper\Constant;


class RequestBeforeQrcode implements ObserverInterface
{



protected $_config;
protected $checkoutSession;
protected $checkoutRegistry;
protected $customerFactory;
protected $customerSession;
protected $gbprimepayLogger;

public function __construct(
    \Magento\Framework\Model\Context $context,
    \Magento\Payment\Helper\Data $paymentData,
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
    \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
    \GBPrimePay\Payments\Model\CustomerFactory $customerFactory,
    \GBPrimePay\Payments\Logger\Logger $gbprimepayLogger,
    $data = []
) {

    $this->gbprimepayLogger = $gbprimepayLogger;
    $this->_config = $configHelper;
    $this->customerFactory = $customerFactory;
    $this->customerSession = $customerSession;
    $this->backendAuthSession = $backendAuthSession;
    $this->sessionQuote = $sessionQuote;
    $this->checkoutSession = $checkoutSession;
    $this->checkoutRegistry = $checkoutRegistry;
    $this->quoteRepository = $quoteRepository;
    $this->quoteManagement = $quoteManagement;
    $this->checkoutData = $checkoutData;

}
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
    $payment = \Magento\Framework\App\ObjectManager::getInstance();
    $order = $payment->get('\Magento\Checkout\Model\Cart');
    $transaction_getid = $order->getQuote()->getId();
    $transaction_quoteid = ''.substr(time(), 4, 5).'00'.$transaction_getid;

    $_gbpmethod_id = $this->_config->getGBPMethod('GBPMethod');
    $_transaction_id = $this->transactiondigit($transaction_getid);

    $amount = $order->getQuote()->getBaseGrandTotal();
    $subamount = $order->getQuote()->getSubtotal();
    $shipamount = $order->getQuote()->getShippingAddress()->getShippingAmount();
    $customer_id = $order->getQuote()->getCustomerId();
        if ($this->_config->getEnvironment() === 'prelive') {
            $url = Constant::URL_QRCODE_TEST;
            $itemtoken = $this->_config->getTestTokenKey();
        } else {
            $url = Constant::URL_QRCODE_LIVE;
            $itemtoken = $this->_config->getLiveTokenKey();
        }
        $customer_full_name = $order->getQuote()->getBillingAddress()->getFirstname() . ' ' . $order->getQuote()->getBillingAddress()->getLastname();
        $itemshippingId = $order->getQuote()->getShippingAddress()->getcustomer_address_id();
        $itemcustomer_id = $order->getQuote()->getCustomerId();
        $itemamount = number_format((($amount * 100)/100), 2, '.', '');
        $itemsubamount = number_format((($subamount * 100)/100), 2, '.', '');
        $itemshippingrate = number_format((($shipamount * 100)/100), 2, '.', '');
        $itemreferenceno = $transaction_quoteid;
        $itemquoteno = $_transaction_id;
        $itemresponseurl = $this->_config->getresponseUrl('response_qrcode');
        $itembackgroundurl = $this->_config->getresponseUrl('background_qrcode');
        $itemcustomerEmail = $order->getQuote()->getCustomerEmail();
        $callgetMerchantId = $this->_config->getMerchantId();
        $_transaction_key = $this->_config->generateID();
        $_item = array(
            "amount" => $itemamount,
            "customerName" => $customer_full_name,
            "customerEmail" => $itemcustomerEmail,
            "merchantDefined1" => $_transaction_key,
            "merchantDefined2" => $itemcustomer_id,
            "merchantDefined3" => $itemquoteno,
        );
        $this->_config->setGBPTransactionITEM($_item);
        $this->_config->setGBPTransactionKEY($_transaction_key);
        $this->_config->setGBPTransactionID($_transaction_id);
	return $this;
	}
    function transactiondigit($string) {
        $strInt = intval($string);
        $strLen = 9;
    	  $strPad = str_pad(($strInt), $strLen, "0", STR_PAD_LEFT);
        return $strPad;
    }
}
