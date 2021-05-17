<?php

class JuniorMaia_Paymee_Model_Observer {

    public function refundOrder($observer) {

        try {

            if(Mage::registry('isRefundRun')) return;
            Mage::register('isRefundRun', true);

            $creditmemo     = $observer->getEvent()->getCreditmemo();
            $order_id       = $creditmemo->getData('order_id');
            $creditmemo_id  = $creditmemo->getId();
            $order          = Mage::getModel('sales/order')->load($order_id);
            $paymee_uuid    = $order->getData('paymee_uuid');

            if (isset($paymee_uuid)) {
                $creditMemo             = Mage::getModel('sales/order_creditmemo')->load($creditmemo_id);
                $adjustment             = $creditMemo->getData('adjustment');
                $totalRefund            = $creditMemo->getData('base_grand_total');

                $response = Mage::helper('juniormaia_paymee/api')->refundOrder($paymee_uuid, $totalRefund);
                Mage::helper('juniormaia_paymee')->logs(" ----- Resposta API ------");
                Mage::helper('juniormaia_paymee')->logs($response);

                $creditmemo->addComment(json_encode($response), false)->save();

                if(!$response["success"]) {
                    Mage::getSingleton('core/session')->addError('PayMee error:<br/>' .  $response['message']);
                } else {
                    Mage::getSingleton('core/session')->addSuccess("Total de {$totalRefund} no pedido {$order->getIncrementId()} UUID {$paymee_uuid} reembolsado com sucesso!");
                }
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }
}