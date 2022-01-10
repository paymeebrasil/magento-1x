<?php

class JuniorMaia_Paymee_WebhookController extends Mage_Core_Controller_Front_Action{

    public function indexAction() {

        try {

            $contents = file_get_contents('php://input');

            Mage::helper('juniormaia_paymee')->logs(" ----- Webhook API ------");
            Mage::helper('juniormaia_paymee')->logs($contents);

            $receipt_payload    = json_decode($contents, true);
            Mage::helper('juniormaia_paymee')->logs($receipt_payload);
            $referenceCode      = $receipt_payload["referenceCode"];
            $orderComment       = null;

            $order = Mage::getModel('sales/order')->loadByIncrementId($referenceCode);
            if(!$order->getId()) {
                var_dump(http_response_code(404));
                return http_response_code(404);
            }

            if (!array_key_exists('newStatus', $receipt_payload)) {
                $orderComment = $contents;
                return false;
            } else {

                switch ($receipt_payload['newStatus']) {
                    case 'PAID':
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                        $this->invoiceOrder($order);
                        $orderComment = "PayMee - Pagamento Aprovado";
                        break;
                    case 'CANCELLED':
                        $orderComment = "PayMee - Pagamento Cancelado";
                        $this->cancelOrder($order);
                        break;
                }
            }

            $status = Mage::getModel('sales/order_status_history')
                ->setOrder($order)
                ->setStatus($order->getStatus())
                ->setComment($orderComment)
                ->setEntityName(Mage_Sales_Model_Order::HISTORY_ENTITY_NAME);
            $order->addStatusHistory($status);
            $order->save();
        } catch(Exception $e) {
            print_r($e->getmessage());
            var_dump(http_response_code(400));
            return http_response_code(400);
        }
    }

    public function cancelOrder($order) {
        Mage::helper('juniormaia_paymee')->logs(" ----- Webhook Order Cancel ------ ");
        if ($order->canCancel()) {
            $order->cancel()->save();
            Mage::helper('juniormaia_paymee')->logs(" ----- Success Order Cancel ------ ");
        } else {
            Mage::helper('juniormaia_paymee')->logs(" ----- Cannot Cancel Order ------ ");
        }
    }

    public function invoiceOrder($order)
    {
        Mage::helper('juniormaia_paymee')->logs(" ----- Webhook Order Invoice ------ ");
        try {
            if ($order->canInvoice()) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

                $transactionSave->save();

                Mage::helper('juniormaia_paymee')->logs(" ----- Success Invoice ------ ");
            } else {
                Mage::helper('juniormaia_paymee')->logs(" ----- Cannot Create Invoice ------ ");
            }
        } catch (Exception $e) {
            Mage::helper($e->getMessage());
        }
    }
}