# GBPrimePay Payment Gateway 

## Installation

#### Step 1

##### Using Composer (recommended)

```
composer require gbprimepay/payments
```

##### Manual Installation  (not recommended)
Install GBPrimePay Payments for Magento 2
 * Download the extension
 * Unzip the file
 * Create a folder {Magento root}/app/code/GBPrimePay/Payments
 * Copy the content from the unzip folder
 * Flush cache

#### Step 2 -  Enable GBPrimePay Payments
```
 php -f bin/magento module:enable --clear-static-content GBPrimePay_Payments
 php -f bin/magento setup:upgrade
 php -f bin/magento setup:static-content:deploy
 php -f bin/magento cache:flush
```

#### Step 3 - Config GBPrimePay_Payments
Log into your Magento Admin, then goto Stores -> Configuration -> GBPrimePay -> GBPrimePay Payments Settings