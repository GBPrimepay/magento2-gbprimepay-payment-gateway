<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;

class Card extends Action
{
    protected $_customerSession;

    protected $cardFactory;

    protected $resultJsonFactory;

    protected $_config;

    protected $gbprimepayLogger;

    protected $jsonFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \GBPrimePay\Payments\Model\CardFactory $cardFactory,
        \GBPrimePay\Payments\Helper\ConfigHelper $configHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \GBPrimePay\Payments\Logger\Logger $assLogger
    ) {
        $this->gbprimepayLogger = $assLogger;
        $this->_customerSession = $customerSession;
        $this->cardFactory = $cardFactory;
        $this->_config = $configHelper;
        $this->jsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    public function _gbprimepayInit()
    {
        try {
            $env = $this->_config->getEnvironment();
        } catch (\Exception $exception) {
            if ($this->_config->getCanDebug()) {
                $this->gbprimepayLogger->addDebug("error gbprimepay init//" . $exception->getMessage());
            }
            throw new \Exception(
                __('Something went wrong. Please try again!')
            );
        }
    }
}
