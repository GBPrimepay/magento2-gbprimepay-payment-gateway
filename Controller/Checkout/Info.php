<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;
use GBPrimePay\Payments\Helper\Constant;

class Info extends \GBPrimePay\Payments\Controller\Checkout
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {

// $this->checkCustomerKey();
// $selected = $this->_config->setGBPMethod('selected_qrcode');



echo "<br>GBPMethod-".$this->_config->getGBPMethod();

echo "<br>getInstructionDirect-".$this->_config->getInstructionDirect();
echo "<br>getInstructionQrcode-".$this->_config->getInstructionQrcode();
echo "<br>getInstructionBarcode-".$this->_config->getInstructionBarcode();

echo "<br>getTitleDirect-".$this->_config->getTitleDirect();
echo "<br>getTitleQrcode-".$this->_config->getTitleQrcode();
echo "<br>getTitleBarcode-".$this->_config->getTitleBarcode();


echo "<br>getSimpleText-".$this->_config->getSimpleText();
echo "<br>getLivePublicKey-".$this->_config->getLivePublicKey();
echo "<br>getLiveSecretKey-".$this->_config->getLiveSecretKey();
echo "<br>getLiveTokenKey-".$this->_config->getLiveTokenKey();
echo "<br>getTestPublicKey-".$this->_config->getTestPublicKey();
echo "<br>getTestSecretKey-".$this->_config->getTestSecretKey();
echo "<br>getTestTokenKey-".$this->_config->getTestTokenKey();
echo "<br>getSellerId-".$this->_config->getSellerId();
echo "<br>getCanDebug-".$this->_config->getCanDebug();
echo "<br>getEnvironment-".$this->_config->getEnvironment();
echo "<br>getActiveDirect-".$this->_config->getActiveDirect();
echo "<br>getActiveQrcode-".$this->_config->getActiveQrcode();
echo "<br>getActiveBarcode-".$this->_config->getActiveBarcode();
echo "<br>checkActivated-".$this->_config->checkActivated();

echo "<br>getresponseUrl-response_direct-".$this->_config->getresponseUrl('response_direct');
echo "<br>getresponseUrl-background_direct-".$this->_config->getresponseUrl('background_direct');
echo "<br>getresponseUrl-response_qrcode-".$this->_config->getresponseUrl('response_qrcode');
echo "<br>getresponseUrl-background_qrcode-".$this->_config->getresponseUrl('background_qrcode');
echo "<br>getresponseUrl-response_barcode-".$this->_config->getresponseUrl('response_barcode');
echo "<br>getresponseUrl-background_barcode-".$this->_config->getresponseUrl('background_barcode');


echo "<br>getImageURLs-".$this->_config->getImageURLs('creditcard');
echo "<br>getImageURLs-".$this->_config->getDemoQrcode();
echo "<br>info";






// exit;

      //
      // echo 'GB Prime Pay : Info develop mode <br>';
      // $getsimpletext = $this->_config->getSimpleText();
      // echo 'getsimpletext '.$getsimpletext.' <br>';
      // $geturl = Constant::URL_DEBIT_AUTHORITY_TEST;
      // echo 'geturl '.$geturl.' <br>';
      //





      $callback = $this->_config->getMerchantId();
      if ($this->_config->getCanDebug()) {
          $this->gbprimepayLogger->addDebug("getMerchantId //" . print_r($callback, true));
      }
      echo '<pre>';
      print_r($callback);

      $url = Constant::URL_CHECKPUBLICKEY_TEST;
      $callback = $this->_config->sendPublicCurl($url, [], 'GET');
      if ($this->_config->getCanDebug()) {
          $this->gbprimepayLogger->addDebug("URL_CHECKPUBLICKEY_TEST //" . print_r($callback, true));
      }
      // echo '<pre>';
      print_r($callback);


if (!empty($callback['merchantId']) && !empty($callback['initialShop']) && !empty($callback['merchantName'])) {
  echo '1-merchantId-initialShop-merchantName:<br>true';

}else{
  echo '2-merchantId-initialShop-merchantName:<br>false';
}


      echo '<br>';

      $url = Constant::URL_CHECKPRIVATEKEY_TEST;
      $callback = $this->_config->sendPrivateCurl($url, [], 'GET');
      if ($this->_config->getCanDebug()) {
          $this->gbprimepayLogger->addDebug("URL_CHECKPRIVATEKEY_TEST //" . print_r($callback, true));
      }
      echo '<br>';
      print_r($callback);
      echo '<br>';

      $url = Constant::URL_CHECKCUSTOMERKEY_TEST;
      $callback = $this->_config->sendTokenCurl($url, [], 'POST');
      if ($this->_config->getCanDebug()) {
          $this->gbprimepayLogger->addDebug("URL_CHECKCUSTOMERKEY_TEST //" . print_r($callback, true));
      }
      echo '<br>sendTokenCurl';
      print_r($callback);
      echo '<br>';


        $data = "{\r\n\"rememberCard\": false,\r\n\"card\": {\r\n\"number\": \"4987654321098769\",\r\n\"expirationMonth\": \"05\",\r\n\"expirationYear\": \"21\",\r\n\"securityCode\": \"111\",\r\n\"name\": \"ปัฐนันท์ เทศแท้\"\r\n}\r\n}";
      $url = Constant::URL_API_TEST;
      $callback = $this->_config->sendAPICurl($url, $data, 'POST');
      if ($this->_config->getCanDebug()) {
          $this->gbprimepayLogger->addDebug("URL_API_TEST //" . print_r($callback, true));
      }
      echo '<br>sendAPICurl';
      print_r($callback);
      echo '<br>';
      echo $callback['resultCode'];
      echo '<br>';
      echo $callback['card']['token'];
      echo '<br>';

      echo '</pre>';
      echo 'qrcode<br>';







      $curl = curl_init();

$field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\nSSOLa0u8a0zcerhmus5ORl8XzrnIJVWjeWa80Tr/c0LOdXYEK9aBvUq/KDdk0b/sElbO2LeaycCJwjtBudEcIh/EeMijAMmHgOjJ7hpE9GiRF7nL3zzcSM61qLUeWjzEL6Zp45okv01zYoUfttAR65jJQUg=\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n840.00\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n000000455\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"payType\"\r\n\r\nF\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"responseUrl\"\r\n\r\nhttps://gbpserv.pay/magento/index.php/checkout/onepage/success/\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"backgroundUrl\"\r\n\r\nhttps://gbpserv.pay/magento/index.php/checkout/afterplaceqrcodeorder\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"detail\"\r\n\r\nmoney\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerName\"\r\n\r\nปัฐนันท์ เทศแท้\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerEmail\"\r\n\r\n9nick@hotmail.co.th\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined1\"\r\n\r\n74h5j-50303-k584m-5i5t2-84w2w-2t214-84\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined2\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined3\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined4\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined5\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.globalprimepay.com/gbp/gateway/qrcode",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $field,
        CURLOPT_HTTPHEADER => array(
          "Cache-Control: no-cache",
          "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {

ob_start();
echo '<img src="data:image/png;base64,' . base64_encode($response) . '">';
// ob_end_flush();
      }




      echo 'qrcode<br>';







      $curl = curl_init();

      $field = "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\nSSOLa0u8a0zcerhmus5ORl8XzrnIJVWjeWa80Tr/c0LOdXYEK9aBvUq/KDdk0b/sElbO2LeaycCJwjtBudEcIh/EeMijAMmHgOjJ7hpE9GiRF7nL3zzcSM61qLUeWjzEL6Zp45okv01zYoUfttAR65jJQUg=\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n840.00\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n000000455\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"payType\"\r\n\r\nF\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"responseUrl\"\r\n\r\nhttps://gbpserv.pay/magento/index.php/checkout/onepage/success/\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"backgroundUrl\"\r\n\r\nhttps://gbpserv.pay/magento/index.php/checkout/afterplaceqrcodeorder\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"detail\"\r\n\r\nmoney\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerName\"\r\n\r\nปัฐนันท์ เทศแท้\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"customerEmail\"\r\n\r\n9nick@hotmail.co.th\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined1\"\r\n\r\n74h5j-50303-k584m-5i5t2-84w2w-2t214-84\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined2\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined3\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined4\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"merchantDefined5\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.globalprimepay.com/gbp/gateway/barcode",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $field,
        CURLOPT_HTTPHEADER => array(
          "Cache-Control: no-cache",
          "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {

      ob_start();
      // echo '<img src="data:image/png;base64,' . base64_encode($response) . '">';
      // echo $response;

// var objbuilder = '';
// objbuilder += ('<object width="100%" height="100%"
// data="data:application/pdf;base64,');
// objbuilder += (myBase64string);
// objbuilder += ('" type="application/pdf" class="internal">');
// objbuilder += ('<embed src="data:application/pdf;base64,');
// objbuilder += (myBase64string);
// objbuilder += ('" type="application/pdf"  />');
// objbuilder += ('</object>');

// echo '<iframe src="data:application/pdf;base64,' . base64_encode($response) . '"></iframe>';
echo '<object width="100%" height="100%" data="data:application/pdf;base64,' . base64_encode($response) . '" type="application/pdf" class="internal"><embed src="data:application/pdf;base64,' . base64_encode($response) . '" type="application/pdf" /></object>';
      // ob_end_flush();
      }










//
//       $curl = curl_init();
//
// curl_setopt_array($curl, array(
//   CURLOPT_URL => "https://api.globalprimepay.com/gbp/gateway/qrcode",
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_FOLLOWLOCATION => true,
//     CURLOPT_SSL_VERIFYPEER => false,
//     CURLOPT_SSL_VERIFYHOST => false,
//     CURLOPT_ENCODING => "",
//     CURLOPT_MAXREDIRS => 10,
//     CURLOPT_TIMEOUT => 30,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => "POST",
//   CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\nSSOLa0u8a0zcerhmus5ORl8XzrnIJVWjeWa80Tr/c0LOdXYEK9aBvUq/KDdk0b/sElbO2LeaycCJwjtBudEcIh/EeMijAMmHgOjJ7hpE9GiRF7nL3zzcSM61qLUeWjzEL6Zp45okv01zYoUfttAR65jJQUg=\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"amount\"\r\n\r\n450.00\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"responseUrl\"\r\n\r\nhttp://test.com\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"detail\"\r\n\r\nmoney\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"referenceNo\"\r\n\r\n2017112800001\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"payType\"\r\n\r\nF\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
//   CURLOPT_HTTPHEADER => array(
//     "Cache-Control: no-cache",
//     "Content-Type: application/json"
//   ),
// ));
//
//
// $response = curl_exec($curl);
// $responseType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
// $err = curl_error($curl);
//
// curl_close($curl);
//
// if ($err) {
//   echo "cURL Error #:" . $err;
// } else {
//   // echo $responseType;
//   // echo '';
//   echo '<img src="data:' . $responseType . ';base64,' . base64_encode($response) . '">';
//   // header('Content-type: image/png');
//   // echo $response;
//   // echo '<pre>';
//   // print_r($response);
// }




      // $callback = $this->_config->sendHttpRequest($url, $field, 'GET');
            // echo "<br><br>.$url.sendCurl<br>";
            // // echo($callback);
            //
            // print_r($callback);

      // $callback = $this->_config->processAPIRequest($url, $field, 'GET');


      // echo "<br><br>.$url.processAPIRequest<br>";
      // echo($callback);


            // echo "<br><br>curl<br>";
            //
            // $body = '{"timestamp":1533263562287,"status":401,"error":"Unauthorized","message":"Full authentication is required to access this resource","path":"/checkPublicKey"}';
            //

      //
      //
      // $curl = curl_init();
      //
      // curl_setopt_array($curl, array(
      //   CURLOPT_URL => "https://api.globalprimepay.com/checkPublicKey",
      //   CURLOPT_RETURNTRANSFER => true,
      //   CURLOPT_FOLLOWLOCATION => true,
      //   CURLOPT_SSL_VERIFYPEER => false,
      //   CURLOPT_SSL_VERIFYHOST => false,
      //   CURLOPT_ENCODING => "",
      //   CURLOPT_MAXREDIRS => 10,
      //   CURLOPT_TIMEOUT => 30,
      //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      //   CURLOPT_CUSTOMREQUEST => "GET",
      //   CURLOPT_HTTPHEADER => array(
      //     "Authorization: Basic WDZuMzhtVzU3ZWptYkprS3FLd2ZmcFYyQTc2Y2ZkQ2g6",
      //     "Cache-Control: no-cache",
      //     "Content-Type: application/json"
      //   ),
      // ));
      //
      // $callback = curl_exec($curl);
      // $err = curl_error($curl);
      //
      // curl_close($curl);
      //
      // if ($err) {
      //   echo "cURL Error #:" . $err;
      // } else {
      //   echo $callback;
      // }
      //
      //
      //



      // $curl = curl_init();
      //
      // curl_setopt_array($curl, array(
      //   CURLOPT_URL => "https://api.globalprimepay.com/v1/tokens",
      //   CURLOPT_RETURNTRANSFER => true,
      //   CURLOPT_ENCODING => "",
      //   CURLOPT_MAXREDIRS => 10,
      //   CURLOPT_TIMEOUT => 30,
      //   CURLOPT_SSL_VERIFYPEER => false,
      //   CURLOPT_SSL_VERIFYHOST => false,
      //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      //   CURLOPT_CUSTOMREQUEST => "POST",
      //   CURLOPT_POSTFIELDS => "{\r\n\"rememberCard\": false,\r\n\"card\": {\r\n\"number\": \"4242424242424242\",\r\n\"expirationMonth\": \"01\",\r\n\"expirationYear\": \"25\",\r\n\"securityCode\": \"111\",\r\n\"name\": \"Card Test\"\r\n}\r\n}",
      //   CURLOPT_HTTPHEADER => array(
      //     "Authorization: Basic WDZuMzhtVzU3ZWptYkprS3FLd2ZmcFYyQTc2Y2ZkQ2g6",
      //     "Cache-Control: no-cache",
      //     "Content-Type: application/json",
      //     "Postman-Token: c62e17d9-71b0-4e42-9d12-72153af06199"
      //   ),
      // ));
      //
      // $callback = curl_exec($curl);
      // $err = curl_error($curl);
      //
      // curl_close($curl);
      //
      // if ($err) {
      //   echo "cURL Error #:" . $err;
      // } else {
      //   echo $callback;
      // }












    }


    public function checkCustomerKey()
    {
      echo "checkCustomerKey";

      echo 'GB Prime Pay : Info develop mode checkCustomerKey<br>';
      exit;
        try {
            $callbackUrl = $this->storeManager->getStore()->getBaseUrl() . 'gbprimepay/checkout/info/checkcustomerkey';
            echo $callbackUrl;
            // exit;

            if ($this->_config->getEnvironment() === 'prelive') {
                $url = Constant::URL_CHECKPUBLICKEY_TEST;
            } else {
                $url = Constant::URL_CHECKPUBLICKEY_TEST;
            }
            $callback = $this->_config->sendCurl("$url", [], 'GET');
            foreach ($callback['callbacks'] as $cb) {
                if ($cb['merchantId'] === true && $cb['initialShop'] === true && $cb['merchantName'] === true) {
                    if ($cb['url'] === $callbackUrl) {
                        // return true;
                        return $cb['url'].'-'.$callbackUrl;
                    }
                }
            }
        } catch (\Exception $e) {
            // return false;
            return $e;
        }

        return false;
    }
}
