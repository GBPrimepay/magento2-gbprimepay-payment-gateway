<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Helper\Context;
use GBPrimePay\Payments\Helper\Constant as Constant;
use Magento\Mtf\Util\Command\Cli;

class ConfigHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_encryptor;
    protected $_urlBuilder;
    protected $_assetRepo;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptorInterface,
        UrlInterface $urlBuilder,
        Repository $assetRepo
    ) {

        parent::__construct($context);
        $this->_encryptor = $encryptorInterface;
        $this->_urlBuilder = $urlBuilder;
        $this->_assetRepo = $assetRepo;
    }


    public function getImageURLs($images)
    {

        if($images=='creditcard'){
          $images = $this->_assetRepo->getUrl("GBPrimePay_Payments::images/creditcard.png");
        }
        if($images=='logo'){
          $images = $this->_assetRepo->getUrl("GBPrimePay_Payments::images/gbprimepay-logo.png");
        }

        return $images;
    }


    public function sendCurl($url, $field, $type)
    {
        if (empty($this->getTestPublicKey())) {
            return false;
            // return ('Please configure your GBPrimePay API Key.');
            // throw new \Magento\Framework\Validator\Exception(__('Please configure your GBPrimePay API Key.'));
        }


        $http_body = NULL;
        $key = base64_encode("{$this->getTestPublicKey()}".":");
        $ch = curl_init($url);

        $request_headers = array(
            "Accept: application/json",
            "Authorization: Basic {$key}",
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        );

        if ($http_body !== NULL) {
            $request_headers[] = "Content-Type: application/json";
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $http_body);
        }




        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);

        $json = json_decode($body, true);
        // echo $json['error'];
        // exit;
        if (isset($json['error'])) {
            return false;
            // return ("Unable to communicatie with the GBPrimePay Payment Server (" . $json['status'] . "): " . $json['message'] . ".");
            // throw new \Magento\Framework\Validator\Exception(__("Unable to communicatie with the GBPrimePay GBPrimePay Payment Server (" . $json['status'] . "): " . $json['message'] . "."));
        }

        curl_close($ch);
        return json_decode($body, true);
        // return $body;
        // return true;
    }
    public function encode($string,$key)
    {
      $key = sha1($key);
      $strLen = strlen($string);
      $keyLen = strlen($key);
      $j = 0;
      $hash = '';
          for ($i = 0; $i < $strLen; $i++) {
              $ordStr = ord(substr($string,$i,1));
              if ($j == $keyLen) { $j = 0; }
              $ordKey = ord(substr($key,$j,1));
              $j++;
              $hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
          }
      return $hash;
    }
    public function generateID()
      {
        $microtime = md5(microtime());
        $encoded = $this->encode($microtime , "GBPrimePay");
        $serial = implode('-', str_split(substr(strtolower($encoded), 0, 32), 5));
        return $serial;
      }
    public function getMerchantId()
    {
        if ($this->getEnvironment() === 'prelive') {
            $configkey = $this->getTestPublicKey();
            $url = Constant::URL_CHECKPUBLICKEY_TEST;
        } else {
            $configkey = $this->getLivePublicKey();
            $url = Constant::URL_CHECKPUBLICKEY_LIVE;
        }

        if (empty($configkey)) {
            return false;
        }

        $field = [];
        $type = 'GET';

        $key = base64_encode("{$configkey}".":");
        $ch = curl_init($url);


        $request_headers = array(
            "Accept: application/json",
            "Authorization: Basic {$key}",
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        );





        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);


        $json = json_decode($body, true);
        if (isset($json['error'])) {
            return false;
        }

        curl_close($ch);
        return $json['merchantId'];
    }
    public function sendPublicCurl($url, $field, $type)
    {
        if ($this->getEnvironment() === 'prelive') {
            $configkey = $this->getTestPublicKey();
        } else {
            $configkey = $this->getLivePublicKey();
        }

        if (empty($configkey)) {
            return false;
        }



        $key = base64_encode("{$configkey}".":");
        $ch = curl_init($url);


        $request_headers = array(
            "Accept: application/json",
            "Authorization: Basic {$key}",
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        );





        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);


        $json = json_decode($body, true);
        if (isset($json['error'])) {
            return false;
        }

        curl_close($ch);
        return json_decode($body, true);
    }

    public function sendPrivateCurl($url, $field, $type)
    {
        if ($this->getEnvironment() === 'prelive') {
            $configkey = $this->getTestSecretKey();
        } else {
            $configkey = $this->getLiveSecretKey();
        }

        if (empty($configkey)) {
            return false;
        }



        $key = base64_encode("{$configkey}".":");
        $ch = curl_init($url);

        $request_headers = array(
            "Accept: application/json",
            "Authorization: Basic {$key}",
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        );





        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);

        $json = json_decode($body, true);
        if (isset($json['error'])) {
            return false;
        }

        curl_close($ch);
        return json_decode($body, true);
    }
    public function sendTokenCurl($url, $field, $type)
    {
        if ($this->getEnvironment() === 'prelive') {
            $configkey = $this->getTestTokenKey();
        } else {
            $configkey = $this->getLiveTokenKey();
        }

        if (empty($configkey)) {
            return false;
        }

        $ch = curl_init($url);

        $request_headers = array(
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
        );


        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "token=".urlencode($configkey));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);

        $json = json_decode($body, true);
        if (isset($json['error'])) {
            return false;
        }

        curl_close($ch);
        return json_decode($body, true);
    }


    public function sendAPICurl($url, $field, $type)
    {
        if ($this->getEnvironment() === 'prelive') {
            $configkey = $this->getTestPublicKey();
        } else {
            $configkey = $this->getLivePublicKey();
        }

        if (empty($configkey)) {
            return false;
        }

        $key = base64_encode("{$configkey}".":");
        $ch = curl_init($url);


        $request_headers = array(
            "Accept: application/json",
            "Authorization: Basic {$key}",
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        );


        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);

        $json = json_decode($body, true);
        if (isset($json['error'])) {
            return false;
        }

        curl_close($ch);
        return json_decode($body, true);
    }

    public function sendCHARGECurl($url, $field, $type)
    {
        if ($this->getEnvironment() === 'prelive') {
            $configkey = $this->getTestSecretKey();
        } else {
            $configkey = $this->getLiveSecretKey();
        }

        if (empty($configkey)) {
            return false;
        }

        $key = base64_encode("{$configkey}".":");
        $ch = curl_init($url);


        $request_headers = array(
            "Accept: application/json",
            "Authorization: Basic {$key}",
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        );


        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);

        $json = json_decode($body, true);
        if (isset($json['error'])) {
            return false;
        }

        curl_close($ch);
        return json_decode($body, true);
    }

    public function sendQRCurl($url, $field, $type)
    {
        if ($this->getEnvironment() === 'prelive') {
            $configkey = $this->getTestPublicKey();
        } else {
            $configkey = $this->getLivePublicKey();
        }

        if (empty($configkey)) {
            return false;
        }

        $ch = curl_init($url);


        $request_headers = array(
            "Cache-Control: no-cache",
            "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        );


        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);

        if ($body=="Incomplete information") {
          $body = 'error : Incomplete information';
        }else{
          // $body = ob_start();'\n<img src="data:image/png;base64,' . base64_encode($body) . '">';
          $body = 'data:image/png;base64,' . base64_encode($body) . '';
        }




        curl_close($ch);
        return $body;
    }
    public function sendBARCurl($url, $field, $type)
    {
        if ($this->getEnvironment() === 'prelive') {
            $configkey = $this->getTestPublicKey();
        } else {
            $configkey = $this->getLivePublicKey();
        }

        if (empty($configkey)) {
            return false;
        }

        $ch = curl_init($url);


        $request_headers = array(
            "Cache-Control: no-cache",
            "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        );


        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $body = curl_exec($ch);

        if ($body=="Incomplete information") {
          $body = 'error : Incomplete information';
        }else{
          // $body = ob_start();'\n<img src="data:image/png;base64,' . base64_encode($body) . '">';
          $body = 'data:application/pdf;base64,' . base64_encode($body) . '';
        }




        curl_close($ch);
        return $body;
    }

    public function getDemoQrcode()
    {
        $images = $this->_assetRepo->getUrl("GBPrimePay_Payments::images/qrcode-demo.png");
        return $images;
    }
    public function getresponseUrl($routeurl)
    {

        if($routeurl=='response_qrcode'){
          $routeurl = $this->_urlBuilder->getUrl("checkout/onepage/success");
        }
        if($routeurl=='background_qrcode'){
          $routeurl = $this->_urlBuilder->getUrl("gbprimepay/checkout/afterplaceqrcodeorder");
        }
        if($routeurl=='response_barcode'){
          $routeurl = $this->_urlBuilder->getUrl("checkout/onepage/success");
        }
        if($routeurl=='background_barcode'){
          $routeurl = $this->_urlBuilder->getUrl("gbprimepay/checkout/afterplacebarcodeorder");
        }

        return $routeurl;
    }


    public function getInstructionDirect()
    {
        return preg_replace('/\s+|\n+|\r/', ' ', $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_direct/instructions'
        ));
    }

    public function getInstructionQrcode()
    {
        return preg_replace('/\s+|\n+|\r/', ' ', $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_qrcode/instructions'
        ));
    }

    public function getInstructionBarcode()
    {
        return preg_replace('/\s+|\n+|\r/', ' ', $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_barcode/instructions'
        ));
    }



    public function getTitleDirect()
    {
        return preg_replace('/\s+|\n+|\r/', ' ', $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_direct/title'
        ));
    }
    public function getLogoDirect()
    {
        $images = $this->_assetRepo->getUrl("GBPrimePay_Payments::images/creditcard.png");
        return $images;
    }

    public function getTitleQrcode()
    {
        return preg_replace('/\s+|\n+|\r/', ' ', $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_qrcode/title'
        ));
    }

    public function getTitleBarcode()
    {
        return preg_replace('/\s+|\n+|\r/', ' ', $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_barcode/title'
        ));
    }

    public function getSimpleText()
    {
        return "text";
    }

    public function getLivePublicKey()
    {
        return $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_required/live_public_key'
        );
    }

    public function getLiveSecretKey()
    {
        return $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_required/live_secret_key'
        );
    }

    public function getLiveTokenKey()
    {
        return $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_required/live_token_key'
        );
    }

    public function getTestPublicKey()
    {
        return $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_required/test_public_key'
        );
    }

    public function getTestSecretKey()
    {
        return $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_required/test_secret_key'
        );
    }

    public function getTestTokenKey()
    {
        return $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_required/test_token_key'
        );
    }

    public function getSellerId()
    {
        return $this->_encryptor->decrypt($this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_required/seller_id'
        ));
    }

    public function getCanDebug()
    {
        return $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_required/debug'
        );
    }

    public function getActiveDirect()
    {
        return $this->scopeConfig->getValue(
            'payment/gbprimepay_direct/active'
        );
    }

    public function getActiveQrcode()
    {
        return $this->scopeConfig->getValue(
            'payment/gbprimepay_qrcode/active'
        );
    }

    public function getActiveBarcode()
    {
        return $this->scopeConfig->getValue(
            'payment/gbprimepay_barcode/active'
        );
    }

    public function getEnvironment()
    {
        return $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_required/environment'
        );
    }

    public function getIsSave()
    {
        return "1";
    }

    public function checkActivated()
    {
        return $this->scopeConfig->getValue(
            'gbprimepay/gbprimepay_direct/check_actived'
        );
    }

    public function convertCountryCodeToIso3($iso2Code)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         * @var \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $countryCollection = $objectManager->create('\Magento\Directory\Model\ResourceModel\Country\Collection');
        $countryCode = $countryCollection->addCountryCodeFilter($iso2Code)
            ->getFirstItem()
            ->getData();

        return $countryCode['iso3_code'] ? $countryCode['iso3_code'] : false;
    }

}
