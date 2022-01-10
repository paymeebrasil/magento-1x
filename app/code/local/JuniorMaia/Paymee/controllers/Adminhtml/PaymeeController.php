<?php

class JuniorMaia_Paymee_Adminhtml_PaymeeController extends Mage_Adminhtml_Controller_Action {

    public function cancelAction()
    {
        try {

            $cancel_ids     = $this->getRequest()->getPost('cancel_ids');
            $collection     = Mage::getResourceModel('sales/order_collection')->addFieldtoFilter('entity_id', array('in' => $cancel_ids));

            if (count($collection) > 0) {
                foreach ($collection as $order) {

                    if ($order->getId()) {
                        try {

                            $paymee_uuid = $order->getData('paymee_uuid');
                            if (isset($paymee_uuid)) {
                                $response = Mage::helper('juniormaia_paymee/api')->cancelOrder($paymee_uuid);
                                Mage::helper('juniormaia_paymee')->logs(" ----- Resposta API ------");
                                Mage::helper('juniormaia_paymee')->logs($response);

                                if(!$response["success"]) {
                                    Mage::getSingleton('core/session')->addError('PayMee error:<br/>' .  $response['message']);
                                } else {
                                    Mage::getSingleton('connect/session')->addSuccess("Pedido {$order->getIncrementId()} UUID {$paymee_uuid} cancelado com sucesso!");
                                }
                            } else {
                                Mage::getSingleton('connect/session')->addError($order->getIncrementId().' não pertence a PayMee');
                            }

                        } catch (Exception $e) {
                            Mage::getSingleton('connect/session')->addError($order->getIncrementId().' not canceled: '.$e->getMessage());
                        }
                    } else {
                        Mage::log('order not found.');
                    }
                }
            } else {
                Mage::log('orders not found.');
            }

            $this->_redirect('*/sales_order/');

        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    public function refundAction()
    {
        try {

            $refund_ids     = $this->getRequest()->getPost('cancel_ids');
            $collection     = Mage::getResourceModel('sales/order_collection')->addFieldtoFilter('entity_id', array('in' => $refund_ids));

            if (count($collection) > 0) {
                foreach ($collection as $order) {

                    if ($order->getId()) {
                        try {

                            $paymee_uuid    = $order->getData('paymee_uuid');
                            $amount         = $order->getData('grand_total');

                            if (isset($paymee_uuid)) {
                                $response = Mage::helper('juniormaia_paymee/api')->refundOrder($paymee_uuid, $amount);
                                Mage::helper('juniormaia_paymee')->logs(" ----- Resposta API ------");
                                Mage::helper('juniormaia_paymee')->logs($response);

                                if(!$response["success"]) {
                                    Mage::getSingleton('core/session')->addError('PayMee error:<br/>' .  $response['message']);
                                } else {
                                    Mage::getSingleton('connect/session')->addSuccess("Pedido {$order->getIncrementId()} UUID {$paymee_uuid} reembolsado com sucesso!");
                                }
                            } else {
                                Mage::getSingleton('connect/session')->addError($order->getIncrementId().' não pertence a PayMee');
                            }

                        } catch (Exception $e) {
                            Mage::getSingleton('connect/session')->addError($order->getIncrementId().' not canceled: '.$e->getMessage());
                        }
                    } else {
                        Mage::log('order not found.');
                    }
                }
            } else {
                Mage::log('orders not found.');
            }

            $this->_redirect('*/sales_order/');

        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    protected function _isAllowed()
    {
        return true;
    }
}