<img src="https://www.globalprimepay.com/dist/images/logo.svg" width="130" />

# GBPrimePay Payment

## Installation

#### Step 1) -  Install GBPrimePay Payments

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

#### Step 2) -  Enable GBPrimePay Payments
```
php bin/magento module:enable GBPrimePay_Payments
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy --force
php bin/magento cache:flush
```

#### Step 3) - Config GBPrimePay Payments
Log into your Magento Admin, then goto  
Stores -> Configuration -> GBPrimePay -> GBPrimePay Payments Settings