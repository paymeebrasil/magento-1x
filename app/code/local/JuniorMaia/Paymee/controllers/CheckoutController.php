<?php

class JuniorMaia_Paymee_CheckoutController extends Mage_Core_Controller_Front_Action{

    public function paymentAction(){

        $session = Mage::getSingleton('checkout/session');
        $approvalRequestSuccess = $session->getApprovalRequestSuccess();

        if (!$session->getLastSuccessQuoteId() && $approvalRequestSuccess != 'partial') {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastSuccessQuoteId();
        $session->setQuoteId($lastQuoteId);

        $_lastOrder         = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
        $customer_id        = $_lastOrder->getCustomerId();
        $_customer          = Mage::getModel('customer/customer')->load($customer_id);
        $_orderData         = $_lastOrder->getData();
        $_paymentData       = $_lastOrder->getPayment();

        $amount             = $_orderData['grand_total'];
        $referenceCode      = $session->getLastRealOrderId();
        $paymentMethod      = $_paymentData->getAdditionalInformation('paymee_banco');
        $agencia            = $_paymentData->getAdditionalInformation('paymee_branch');
        $conta              = $_paymentData->getAdditionalInformation('paymee_account');
        $paymee_document    = $_paymentData->getAdditionalInformation('paymee_cpf');

        $data = array(
            "currency"          => "BRL",
            "amount"            => (float)$amount,
            "referenceCode"     => $referenceCode,
            "maxAge"            => Mage::helper('juniormaia_paymee')->getMaxAge(),
            "paymentMethod"     => $paymentMethod,
            "callbackURL"       => Mage::getUrl('paymee/webhook/index/'),
            "shopper" => array(
                "id" => $_customer->getId(),
                "name" => $_orderData['customer_firstname'].' '.$_orderData['customer_lastname'],
                "email" => $_orderData['customer_email'],
                "document" => array(
                    "type"      => "CPF",
                    "number"    => $paymee_document,
                ),
                "phone" => array(
                    "type"      => "MOBILE",
                    "number"    => $_lastOrder->getBillingAddress()->getTelephone(),
                ),
                "bankDetails" => array(
                    "branch"    => $agencia,
                    "account"   => $conta,
                )
            )
        );

        Mage::helper('juniormaia_paymee')->logs(" ----- Enviando Dados API ------");
        Mage::helper('juniormaia_paymee')->logs($data);

        $response = Mage::helper('juniormaia_paymee/api')->checkout($data);

        Mage::helper('juniormaia_paymee')->logs(" ----- Resposta API ------");
        Mage::helper('juniormaia_paymee')->logs($response);

        if(!$response["success"]) {
            Mage::getSingleton('core/session')->addError('Erro na comunicação com a PayMee.<br/>' .  $response['message']);
            $this->_redirect('checkout/cart');
        } else {
            if ($_lastOrder->getId()) {
                $response_payload   = json_decode($response["response_payload"], true);
                $_response           = $response_payload['response'];

                Mage::helper('juniormaia_paymee')->logs($_response);

                $saleCode       = $_response['saleCode'];
                $uuid           = $_response['uuid'];
                $referenceCode  = $_response['referenceCode'];

                $_lastOrder->setPaymeeUuid($uuid);
                $_lastOrder->setPaymeeSalecode($saleCode);
                $_lastOrder->setPaymeeReferencecode($referenceCode);
                $_lastOrder->save();

                if (Mage::helper('juniormaia_paymee')->getCommentOrder()) {
                    $_lastOrder->addStatusHistoryComment($response_payload);
                    $_lastOrder->save();
                }
                
                Mage::getSingleton('core/session')->setData('paymee_instructions', $response["response_payload"]);

            }
        }

        if($this->getRequest()->getParam('increment_id')) {
            die(__METHOD__);
        }

        $this->_redirect('checkout/onepage/success');
    }
}