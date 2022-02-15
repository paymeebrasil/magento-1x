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
        $paymentCode        = $_paymentData->getMethod();

        $amount             = $_orderData['grand_total'];
        $referenceCode      = $session->getLastRealOrderId();
        $paymentMethod      = $_paymentData->getAdditionalInformation('paymee_banco');
        $agencia            = $_paymentData->getAdditionalInformation('paymee_branch');
        $conta              = $_paymentData->getAdditionalInformation('paymee_account');
        $paymee_document    = $_paymentData->getAdditionalInformation('paymee_cpf');

        if ($paymentCode == 'juniormaia_paymee_pix') {
            $paymentMethod = 'PIX';
        }

        $data = array(
            "currency"          => "BRL",
            "amount"            => (float)$amount,
            "referenceCode"     => $referenceCode,
            "discriminator"     => Mage::helper('juniormaia_paymee')->getDiscriminator(),
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
            )
        );

        if ($paymentCode == 'juniormaia_paymee_transfer') {
            $data["shopper"]["bankDetails"] = array(
                "branch"    => $agencia,
                "account"   => $conta,
            );
        }

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
                $_response          = $response_payload['response'];

                Mage::helper('juniormaia_paymee')->logs($_response);

                $saleCode       = $_response['saleCode'];
                $uuid           = $_response['uuid'];
                $referenceCode  = $_response['referenceCode'];

                if ($paymentCode == 'juniormaia_paymee_pix') {
                    $qrCode = $_response['instructions']['qrCode']['url'];
                    $plain  = $_response['instructions']['qrCode']['plain'];
                    $_lastOrder->getPayment()->setAdditionalInformation('paymee_pix_qrcode', $qrCode);
                    $_lastOrder->getPayment()->setAdditionalInformation('paymee_pix_plain', $plain);
                }

                $_lastOrder->setPaymeeUuid($uuid);
                $_lastOrder->setPaymeeSalecode($saleCode);
                $_lastOrder->setPaymeeReferencecode($referenceCode);
                $_lastOrder->save();

                if (Mage::helper('juniormaia_paymee')->getCommentOrder()) {
                    $_lastOrder->addStatusHistoryComment($response["response_payload"]);
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

    public function checkPixAction() {
        try {
            $uuid = $this->getRequest()->getParam('uuid');
            if ($uuid) {
                $paymentStatus = Mage::helper('juniormaia_paymee/api')->checkTransactionStatus($uuid);
                echo $paymentStatus;
                return $paymentStatus;
            }
        } catch (Exception $e) {
            Mage::helper('juniormaia_paymee')->logs($e->getmessage());
        }
    }

    public function loansAction() {
        try {
            $cpf    = $this->getRequest()->getParam('cpf');
            $quote  = Mage::getModel('checkout/session')->getQuote();

            if (!$cpf || $cpf == '') {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'informe o CPF'
                    )
                );
                return false;
            }

            if ($quote->getId()) {
                $amount = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
                $cpf    = preg_replace("/[^0-9]/", "", $cpf);

                $data = array(
                    "customer" => array(
                        "document" => array(
                            "type" => (strlen((string)$cpf) > 11 ? 'CNPJ' : 'CPF'),
                            "number" => $cpf
                        ),
                    ),
                    "amount" => (float)$amount
                );

                Mage::helper('juniormaia_paymee')->logs("--- Call Loans Simulation ---");
                Mage::helper('juniormaia_paymee')->logs($data);

                $response = Mage::helper('juniormaia_paymee/api')->loansSimulation($data);
                if (isset($response['hasProposal']) && $response['hasProposal'] == 1) {
                    $proposal_id    = $response['proposals'][0]['proposal_id'];
                    $terms          = $response['proposals'][0]['terms'];

                    $propostas = array();
                    foreach ($terms as $props) {
                        $_proposta = array(
                            'label'         => $props['label'],
                            'final_amount'   => $props['final_amount']
                        );
                        $propostas[] = $_proposta;
                    }

                    $return = array(
                            'success'       => true,
                            'proposal_id'   => $proposal_id,
                            'proposals'     => $propostas
                    );

                    //Saving proposal_id
                    Mage::getSingleton('core/session')->setPaymeeProposalId($proposal_id);
                    Mage::helper('juniormaia_paymee')->logs($return);
                    echo json_encode($return);
                } else {
                    echo json_encode(
                        array(
                            'success' => false,
                            'message' => 'Não houve nenhum proposta encontrada para seu '.(strlen((string)$cpf) > 11 ? 'CNPJ' : 'CPF')
                        )
                    );
                }
            } else {
                echo json_encode(
                    array(
                        'success' => false,
                        'message' => 'No quote available.'
                    )
                );
            }
        } catch (Exception $e) {
            Mage::helper('juniormaia_paymee')->logs($e->getmessage());
        }
    }

    public function loansCreateAction() {
        try {
            $session = Mage::getSingleton('checkout/session');
            $approvalRequestSuccess = $session->getApprovalRequestSuccess();

            if (!$session->getLastSuccessQuoteId() && $approvalRequestSuccess != 'partial') {
                $this->_redirect('checkout/cart');
                return;
            }

            $lastQuoteId = $session->getLastSuccessQuoteId();
            $session->setQuoteId($lastQuoteId);

            $_lastOrder         = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            $_orderData         = $_lastOrder->getData();
            $_paymentData       = $_lastOrder->getPayment();
            $terms              = $_paymentData->getAdditionalInformation('paymee_boleto_installments');
            $referenceCode      = $session->getLastRealOrderId();
            $proposal_id        = Mage::getSingleton('core/session')->getPaymeeProposalId();

            $data = array(
                "proposal_id" => $proposal_id,
                "reference_code"    => $referenceCode,
                "terms"             => str_replace('x', '', $terms),
                "discriminator"     => Mage::helper('juniormaia_paymee')->getDiscriminator(),
                "max_age"           => Mage::helper('juniormaia_paymee')->getMaxAge(),
                "customer"          => array(
                    "email"         => $_orderData['customer_email'],
                    "mobile_number" => $_lastOrder->getBillingAddress()->getTelephone(),
                    "address"       => array(
                        "zipcode"       => preg_replace("/[^0-9]/", "", $_lastOrder->getBillingAddress()->getPostcode()),
                        "street"        => $_lastOrder->getBillingAddress()->getStreet(1),
                        "number"        => $_lastOrder->getBillingAddress()->getStreet(2),
                        "complement"    => $_lastOrder->getBillingAddress()->getStreet(3),
                        "neighborhood"  => $_lastOrder->getBillingAddress()->getStreet(4),
                        "city"          => $_lastOrder->getBillingAddress()->getCity(),
                        "state"         => $_lastOrder->getBillingAddress()->getRegionCode()
                    ),
                )
            );

            Mage::helper('juniormaia_paymee')->logs("--- loans create ---");
            Mage::helper('juniormaia_paymee')->logs($data);

            $response = Mage::helper('juniormaia_paymee/api')->loansCreate($data);

            Mage::helper('juniormaia_paymee')->logs(" ----- Resposta API ------");
            Mage::helper('juniormaia_paymee')->logs($response);

            if(!$response["uuid"]) {
                Mage::getSingleton('core/session')->addError('Erro na comunicação com a PayMee.<br/>' .  $response['message']);
                $this->_redirect('checkout/cart');
            } else {
                if ($_lastOrder->getId()) {
                    $_lastOrder->setPaymeeUuid($response["uuid"]);
                    $_lastOrder->setPaymeeSalecode($response["id"]);
                    $_lastOrder->setPaymeeReferencecode($response["referenceCode"]);
                    $_lastOrder->save();

                    if (Mage::helper('juniormaia_paymee')->getCommentOrder()) {
                        $_lastOrder->addStatusHistoryComment(json_encode($response));
                        $_lastOrder->save();
                    }

                    Mage::getSingleton('core/session')->setData('paymee_boleto_uuid', $response["uuid"]);
                }
            }

            $this->_redirect('checkout/onepage/success');

        } catch (Exception $e) {
            Mage::helper('juniormaia_paymee')->logs($e->getMessage());
        }
    }

    public function uploadDocumentsAction() {
        try {
            Mage::helper('juniormaia_paymee')->logs(" ----- Anexando Documentos ------");
            Mage::helper('juniormaia_paymee')->logs($_FILES);

            if (
                isset($_FILES['file_upload_selfie']['name']) &&
                isset($_FILES['file_upload_document']['name'])
            ) {
                $document       = $_FILES['file_upload_document'];
                $selfie          = $_FILES['file_upload_selfie'];
                $sourceFilePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'paymee_uploads';

                if (!is_dir($sourceFilePath)) {
                    mkdir($sourceFilePath);
                    chmod($sourceFilePath, 0777);
                }

                $uploadDoc             = false;
                $uploadSelfie           = false;
                $imagePathSelfie        = null;
                $imagePathDocument     = null;

                /* Upload document */
                if ($document["error"] == UPLOAD_ERR_OK) {
                    $name       = $document["name"];
                    $docImg     = $sourceFilePath . DS . $name;

                    move_uploaded_file($document["tmp_name"], $docImg);
                    if (file_exists($docImg)) {
                        $uploadDoc          = true;
                        $imagePathDocument  = $docImg;
                    }
                }

                /* Upload selfie */
                if ($selfie["error"] == UPLOAD_ERR_OK) {
                    $name       = $selfie["name"];
                    $selfieImg   = $sourceFilePath . DS . $name;

                    move_uploaded_file($selfie["tmp_name"], $selfieImg);
                    if (file_exists($selfieImg)) {
                        $uploadSelfie       = true;
                        $imagePathSelfie    = $selfieImg;
                    }
                }

                $return = array();

                if (!$uploadDoc && !$uploadSelfie) {
                    $return['message']  = 'Falha ao salvar a imagem dos documentos, por favor verifique o tamanho da imagem, formato e extensão.';
                    $return['success']  = 0;
                }

                if (!$uploadDoc || !$uploadSelfie) {
                    $return['message']  = 'Falha ao salvar a imagem do documento, por favor verifique o tamanho da imagem, formato e extensão.';
                    $return['success']  = 2;
                }

                if ($uploadDoc && $uploadSelfie) {
                    $proposal_id = Mage::getSingleton('core/session')->getPaymeeProposalId();
                    $response1 = Mage::helper('juniormaia_paymee/api')->sendDocuments('selfie', $imagePathSelfie, $proposal_id);
                    $response2 = Mage::helper('juniormaia_paymee/api')->sendDocuments('document', $imagePathDocument, $proposal_id);

                    Mage::helper('juniormaia_paymee')->logs($response1);
                    Mage::helper('juniormaia_paymee')->logs($response2);

                    if ($response1['response']['success'] && $response2['response']['success']) {
                        $return['message']  = 'Imagens anexadas com sucesso!';
                        $return['success']  = 1;
                    } else {
                        $return['message']  = $response1['response']['message'].' - '.$response2['response']['message'];
                        $return['success']  = 2;
                    }
                }

                echo json_encode($return, JSON_FORCE_OBJECT);

            } else {
                Mage::helper('juniormaia_paymee')->logs("Arquivos não recebidos no backend");
                echo json_encode(array(
                    'success' => 0,
                    'message' => "Arquivos não recebidos no servidor, entre em contato com o suporte!"
                ));
            }
        } catch (Exception $e) {
            Mage::helper('juniormaia_paymee')->logs($e->getMessage());
            echo json_encode(array(
                'success' => 0,
                'message' => $e->getMessage()
            ));
        }
    }
}