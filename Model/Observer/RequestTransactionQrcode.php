<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use GBPrimePay\Payments\Helper\Constant;


class RequestTransactionQrcode implements ObserverInterface
{



protected $_config;
protected $checkoutSession;
protected $customerFactory;
protected $customerSession;
protected $gbprimepayLogger;

public function __construct(
    \Magento\Framework\Model\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Payment\Helper\Data $paymentData,
    \Magento\Payment\Model\Method\Logger $logger,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Backend\Model\Auth\Session $backendAuthSession,
    \Magento\Backend\Model\Session\Quote $sessionQuote,
    \Magento\Checkout\Model\Session $checkoutSession,
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
    $this->quoteRepository = $quoteRepository;
    $this->quoteManagement = $quoteManagement;
    $this->checkoutData = $checkoutData;

}
	public function execute(\Magento\Framework\Event\Observer $observer)
	{

    if ($this->_config->getCanDebug()) {
      $this->gbprimepayLogger->addDebug("Qrcode event start//");
    }
    $payment = \Magento\Framework\App\ObjectManager::getInstance();
    $order = $payment->get('\Magento\Checkout\Model\Session');



    $lastorder = $this->checkoutSession->getLastRealOrder();
    $lastorderId = $lastorder->getIncrementId();


    $amount = $order->getQuote()->getBaseGrandTotal();
    $subamount = $order->getQuote()->getSubtotal();
    $shipamount = $order->getQuote()->getShippingAddress()->getShippingAmount();
    $customer_id = $order->getQuote()->getCustomerId();


    $itemsCollection = $order->getQuote()->getItemsCollection();
    $itemsVisible = $order->getQuote()->getAllVisibleItems();
    $productitems = $order->getQuote()->getAllItems();
    $productitemsstr = '_item_';

    foreach($productitems as $_item) {
        $itemgetPrice = number_format((($_item->getPrice() * 100)/100), 2, '.', '');
        $productitemsstr .= ''.$_item->getProductId().'--'.$_item->getQty().'--'.$itemgetPrice.'::';
    }










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
$itemreferenceno = $this->plusdigit($lastorderId);
$itemresponseurl = $this->_config->getresponseUrl('response_qrcode');
$itembackgroundurl = $this->_config->getresponseUrl('background_qrcode');
$itemcustomerEmail = $order->getQuote()->getCustomerEmail();
$callgetMerchantId = $this->_config->getMerchantId();
$callgenerateID = $this->_config->generateID();

$productitemsstr .= '_total_';
$productitemsstr .= ''.$itemsubamount.'--'.$itemshippingrate.'--'.$itemamount.'::';




$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\n$itemtoken\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n$itemamount\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n$itemreferenceno\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"payType\"\r\n\r\nF\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"responseUrl\"\r\n\r\n$itemresponseurl\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"backgroundUrl\"\r\n\r\n$itembackgroundurl\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"detail\"\r\n\r\n$callgetMerchantId\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerName\"\r\n\r\n$customer_full_name\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerEmail\"\r\n\r\n$itemcustomerEmail\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined1\"\r\n\r\n$callgenerateID\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined2\"\r\n\r\n$itemcustomer_id\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined3\"\r\n\r\n$itemshippingId\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined4\"\r\n\r\n$productitemsstr\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined5\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

if ($this->_config->getCanDebug()) {
$this->gbprimepayLogger->addDebug("field //" . print_r($field, true));
}

$callback = $this->_config->sendQRCurl("$url", $field, 'POST');



      if ($callback=="Incomplete information") {
      }else{
            $this->checkoutSession->setGenerateQrcode($callback);
      }

		return $this;
	}
  function plusdigit($string) {
    $strInt = intval($string);
    $strLen = strlen($string);
    $strPad = str_pad(($strInt+1), $strLen, "0", STR_PAD_LEFT);
    return $strPad;
}
}
