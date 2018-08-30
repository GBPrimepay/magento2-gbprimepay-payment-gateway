<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2018 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Controller\Customer;

use GBPrimePay\Payments\Controller\Customer\Card as Card;

class Del extends Card
{
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $result = $this->jsonFactory->create();
            $tokenid = null;
            $id = $this->getRequest()->getParam('id');
            $model = $this->cardFactory->create()->load($id);
            $data = $model->getData();
            $tokenid = $data['tokenid'];

            $this->_gbprimepayInit();

            $out = [];

            try {
                if ($id) {
                    $model->delete();
                    $model->save();
                    $out = [
                        'success' => true
                    ];
                }
            } catch (\Exception $error) {
                $model->delete();
                $model->save();
                $out = [
                    'success' => true
                ];
            }

            $result->setData($out);

            return $result;
        }

        return "er";
    }
}
