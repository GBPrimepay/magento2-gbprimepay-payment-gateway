<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Plugin;

class CsrfValidatorSkip {
    const CONTROLLER_MODULE = '/index.php/gbprimepay/checkout';
    const CONTROLLER_NAME = '/gbprimepay/checkout';
    /**
     * @param \Magento\Framework\App\Request\CsrfValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ActionInterface $action
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        if ($request->getModuleName() == 'gbprimepay') {
            return;
        }
        if (strpos($request->getOriginalPathInfo(), '/gbprimepay/checkout') !== false) {
            return;
        }
        if ($request->getControllerModule() == self::CONTROLLER_MODULE
            && $request->getControllerName() == self::CONTROLLER_NAME) {
            return;
        }
        $requestUrlCforge = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        if ($requestUrlCforge == self::CONTROLLER_MODULE
            || $requestUrlCforge == self::CONTROLLER_NAME) {
            return;
        }
        $proceed($request, $action);
    }
    
}
