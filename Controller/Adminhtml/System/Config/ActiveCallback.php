<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Adminhtml\System\Config;

use GBPrimePay\Payments\Logger\Logger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use GBPrimePay\Payments\Helper\Constant;

class ActiveCallback extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    protected $_config;

    protected $storeManager;

    protected $gbprimepayLogger;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        Logger $logger
    ) {
        $this->gbprimepayLogger = $logger;
        $this->_config = $configHelper;
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        try {
            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->jsonFactory->create();

            $check = $this->checkEnable();
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("data-callback //" . print_r($check, true));
            }

            switch ($check) {
                case 0:
                    return $result->setData([
                        'success' => true,
                        'error' => false,
                        'message' => "Actived, Credit Card is already set"
                    ]);
                    break;
                case 2:
                    $response = $this->createCallback();
                    break;
                case 3:
                      return $result->setData([
                          'success' => false,
                          'error' => true,
                          'message' => "Missing credentials in config"
                      ]);
                    break;
                default:
                    $response = $this->updateCallback($check);
                    break;




//                    return $result->setData([
//                        'success' => false,
//                        'error' => true,
//                        'message' => "Callback is already set"
//                    ]);




            }
            if (isset($response['errors'])) {
                if (isset($response['errors']['url']) && is_array($response['errors']['url'])) {
                    $errorMsg = implode(" . ", $response['errors']['url']);

                    return $result->setData([
                        'success' => false,
                        'error' => true,
                        'message' => $errorMsg
                    ]);
                }
            } else {
                return $result->setData([
                    'success' => true,
                    'error' => false,
                    'message' => "Callback url is updated"
                ]);
            }
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug($exception->getMessage());
            }
        }
    }

    public function updateCallback($callbackId)
    {
        $callbackUrl = $this->storeManager->getStore()->getBaseUrl() . 'gbprimepay/checkout/callback';
        if ($this->_config->getEnvironment() === 'prelive') {
            $url = Constant::URL_CHECKPUBLICKEY_TEST;
        } else {
            $url = Constant::URL_CHECKPUBLICKEY_LIVE;
        }
        $urlDel = $url . "/" . $callbackId;
        $response = $this->_config->sendPublicCurl($urlDel, [], "DELETE");
        if ((isset($response['callbacks'])) && ($response['callbacks'] == 'Successfully redacted')) {
            //delete successful
            return $this->createCallback();
        }

        return $this->_config->sendPublicCurl($urlDel, [], "DELETE");
    }

    public function createCallback()
    {
        $callbackUrl = $this->storeManager->getStore()->getBaseUrl() . 'gbprimepay/checkout/callback';
        if ($this->_config->getEnvironment() === 'prelive') {
            $url = Constant::URL_CHECKPUBLICKEY_TEST;
        } else {
            $url = Constant::URL_CHECKPUBLICKEY_LIVE;
        }

        return $this->_config->sendPublicCurl($url, [], 'GET');
    }

    public function checkEnable()
    {
      if ($this->_config->getActiveCreditCard()) {
        $callbackUrl = $this->storeManager->getStore()->getBaseUrl() . 'gbprimepay/checkout/callback';

        if ($this->_config->getEnvironment() === 'prelive') {
            $url = Constant::URL_CHECKPUBLICKEY_TEST;
        } else {
            $url = Constant::URL_CHECKPUBLICKEY_LIVE;
        }
        $callback = $this->_config->sendPublicCurl("$url", [], 'GET');



        if ($this->_config->getCanDebug()) {
            $this->gbprimepayLogger->addDebug("sendPublicCurl-callback //" . print_r($callback, true));
        }



            if (!empty($callback['merchantId']) && !empty($callback['initialShop']) && !empty($callback['merchantName'])) {
                    if ($this->_config->getEnvironment() === 'prelive') {
                        $url = Constant::URL_CHECKPRIVATEKEY_TEST;
                    } else {
                        $url = Constant::URL_CHECKPRIVATEKEY_LIVE;
                    }
                    $callback = $this->_config->sendPrivateCurl("$url", [], 'GET');
                        if (!empty($callback['merchantId']) && !empty($callback['initialShop']) && !empty($callback['merchantName'])) {
                                return 0;
                        }else{
                          return 3;
                        }
            }else{
              return 3;
            }

        return 2;
    }else{
      return 3;
    }
  }
}
