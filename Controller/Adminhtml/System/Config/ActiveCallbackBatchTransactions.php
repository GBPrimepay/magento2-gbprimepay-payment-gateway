<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Adminhtml\System\Config;

use GBPrimePay\Payments\Logger\Logger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use GBPrimePay\Payments\Helper\Constant;

class ActiveCallbackBatchTransactions extends Action
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

            switch ($check) {
                case 0:
                    //callback was created
                    return $result->setData([
                        'success' => true,
                        'error' => false,
                        'message' => "Callback was created"
                    ]);
                    break;
                case 2:
                    //cannot find any callback of batch transaction object
                    $response = $this->createCallback();
                    break;
                default:
                    //need enable callback for correct response url
                    $response = $this->updateCallback($check);
                    break;
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
                    'message' => "Callback url is updated",
                    'data' => json_encode($response)
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
        $callbackUrl = $this->storeManager->getStore()->getBaseUrl() . 'gbprimepay/checkout/batchTransactionsCallbackResponse';
        if ($this->_config->getEnvironment() === 'prelive') {
            $url = Constant::URL_CALLBACK_TEST;
        } else {
            $url = Constant::URL_CALLBACK;
        }
        $urlDel = $url . "/" . $callbackId;
        $response = $this->_config->sendCurl($urlDel, [
            'description' => 'Batch Transactions CallBack of Magento 2',
            'url' => $callbackUrl,
            'enabled' => 'true'
        ], "PATCH");

        return $response;
    }

    public function createCallback()
    {
        $callbackUrl = $this->storeManager->getStore()->getBaseUrl() . 'gbprimepay/checkout/batchTransactionsCallbackResponse';
        if ($this->_config->getEnvironment() === 'prelive') {
            $url = Constant::URL_CALLBACK_TEST;
        } else {
            $url = Constant::URL_CALLBACK;
        }
        $data = [
            'description' => 'Batch Transactions CallBack of Magento 2',
            'url' => $callbackUrl,
            'object_type' => 'batch_transactions',
            'enabled' => 'true'
        ];

        return $this->_config->sendCurl($url, $data, 'POST');
    }

    public function checkEnable()
    {
        $callbackUrl = $this->storeManager->getStore()->getBaseUrl() . 'gbprimepay/checkout/batchTransactionsCallbackResponse';

        if ($this->_config->getEnvironment() === 'prelive') {
            $url = Constant::URL_CALLBACK_TEST;
        } else {
            $url = Constant::URL_CALLBACK;
        }
        $callback = $this->_config->sendCurl("$url", [], 'GET');
        foreach ($callback['callbacks'] as $cb) {
            if ($cb['enabled'] === true && $cb['object_type'] === 'batch_transactions') {
                if ($cb['url'] === $callbackUrl) {
                    return 0;
                } else {
                    return $cb['id'];
                }
            }
        }

        return 2;
    }
}
