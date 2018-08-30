<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Helper;

class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    public function delete($uri)
    {
        $this->makeRequest("DELETE", $uri);
    }

    public function patch($uri, $params)
    {
        $this->makeRequest("PATCH", $uri, $params);
    }
}
