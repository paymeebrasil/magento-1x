<?php

class JuniorMaia_Paymee_Helper_Api extends Mage_Core_Helper_Abstract
{
    public function checkout($data) {
        try {

            /*
             * Production Environment
             * https://api.paymee.com.br/
             * Sandbox Environment
             * https://apisandbox.paymee.com.br/
             */

            Mage::helper('juniormaia_paymee')->logs(" ----- Chamando API ------");

            $url = 'https://api.paymee.com.br/';
            if (Mage::helper('juniormaia_paymee')->getEnvironmentSandbox()) {
                $url = 'https://apisandbox.paymee.com.br/';
            }

            $url            = $url."v1.1/checkout/transparent/";
            $x_api_key      = Mage::helper('juniormaia_paymee')->getApiKey();
            $x_api_token    = Mage::helper('juniormaia_paymee')->getApiToken();

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data, true),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "x-api-key: $x_api_key",
                    "x-api-token: $x_api_token"
                ),
            ));

            $response   = curl_exec($curl);
            $err        = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $cURLErrorMessage = "PayMee - cURL Error #:" . $err;
                return array(
                    "success" => false,
                    "response_payload" => $response,
                    "message" => $cURLErrorMessage
                );
            }

            $paymee_response = json_decode($response, true);
            if($paymee_response['status'] !== 0) {
                return array(
                    "success" => false,
                    "response_payload" => $response,
                    "message" => $paymee_response['errors'][0]['message']
                );
            }

            return array(
                "success" => true,
                "response_payload" => $response,
                "message" => "success"
            );
        }
        catch(Exception $e) {
            return array(
                "success" => false,
                "response_payload" => $response,
                "message" => $e->getMessage()
            );
        }
    }

    public function loansSimulation($data) {
        try {

            /*
             * Production Environment
             * https://api.paymee.com.br/
             * Sandbox Environment
             * https://apisandbox.paymee.com.br/
             */

            Mage::helper('juniormaia_paymee')->logs(" ----- Chamando API ------");

            $url = 'https://api.paymee.com.br/';
            if (Mage::helper('juniormaia_paymee')->getEnvironmentSandbox()) {
                $url = 'https://apisandbox.paymee.com.br/';
            }

            $url            = $url."v1.1/loans/simulation/";
            $x_api_key      = Mage::helper('juniormaia_paymee')->getApiKey();
            $x_api_token    = Mage::helper('juniormaia_paymee')->getApiToken();

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data, true),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "x-api-key: $x_api_key",
                    "x-api-token: $x_api_token"
                ),
            ));

            $response   = curl_exec($curl);
            $err        = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $cURLErrorMessage = "PayMee - cURL Error #:" . $err;
                return array(
                    "success" => false,
                    "response_payload" => $response,
                    "message" => $cURLErrorMessage
                );
            }

            return json_decode($response, true);
        }
        catch(Exception $e) {
            return array(
                "success" => false,
                "response_payload" => $response,
                "message" => $e->getMessage()
            );
        }
    }

    public function loansCreate($data) {
        try {
            Mage::helper('juniormaia_paymee')->logs(" ----- Chamando API ------");

            $url = 'https://api.paymee.com.br/';
            if (Mage::helper('juniormaia_paymee')->getEnvironmentSandbox()) {
                $url = 'https://apisandbox.paymee.com.br/';
            }

            $url            = $url."v1.1/loans/create/";
            $x_api_key      = Mage::helper('juniormaia_paymee')->getApiKey();
            $x_api_token    = Mage::helper('juniormaia_paymee')->getApiToken();

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => json_encode($data, true),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "x-api-key: $x_api_key",
                    "x-api-token: $x_api_token"
                ),
            ));

            $response   = curl_exec($curl);
            $err        = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $cURLErrorMessage = "PayMee - cURL Error #:" . $err;
                return array(
                    "success" => false,
                    "response_payload" => $response,
                    "message" => $cURLErrorMessage
                );
            }

            return json_decode($response, true);
        }
        catch(Exception $e) {
            return array(
                "success" => false,
                "response_payload" => $response,
                "message" => $e->getMessage()
            );
        }
    }

    public function checkTransactionStatus($uuid) {

        try {
            Mage::helper('juniormaia_paymee')->logs(" ----- Check Payment Status API ------");

            $x_api_key      = Mage::helper('juniormaia_paymee')->getApiKey();
            $x_api_token    = Mage::helper('juniormaia_paymee')->getApiToken();

            $url = 'https://api.paymee.com.br/';
            if (Mage::helper('juniormaia_paymee')->getEnvironmentSandbox()) {
                $url = 'https://apisandbox.paymee.com.br/';
            }

            $url = $url."v1.1/transactions/{$uuid}";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_HTTPHEADER => array(
                    "x-api-key: $x_api_key",
                    "x-api-token: $x_api_token"
                ),
            ));

            $response = curl_exec($curl);

            Mage::helper('juniormaia_paymee')->logs($response);

            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                Mage::helper('juniormaia_paymee')->logs($err);
                return false;
            }

            $responseData = json_decode($response, true);

            if(!array_key_exists('situation', $responseData)) {
                return false;
            }

            Mage::helper('juniormaia_paymee')->logs($responseData['situation']);

            if ($responseData['situation'] == "PAID") {
                $this->changeOrderStatus($responseData['referenceCode']);
            }

            return $responseData['situation'];
        }
        catch(Exception $e) {
            print_r($e->getmessage());
            Mage::helper('juniormaia_paymee')->logs($e->getmessage());
            return false;
        }
    }

    public function changeOrderStatus($orderIncrement) {
        try {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrement);
            if ($order->getStatus() == "pending") {
                Mage::helper('juniormaia_paymee')->invoiceOrder($order);
            }
        } catch (Exception $e) {
            Mage::helper('juniormaia_paymee')->logs($e->getMessage());
        }
    }

    public function cancelOrder($uuid) {
        try {

            Mage::helper('juniormaia_paymee')->logs(" ----- Chamando API ------");

            $url = 'https://api.paymee.com.br/';
            if (Mage::helper('juniormaia_paymee')->getEnvironmentSandbox()) {
                $url = 'https://apisandbox.paymee.com.br/';
            }

            $url            = $url."v1.1/transactions/{$uuid}/void";
            $x_api_key      = Mage::helper('juniormaia_paymee')->getApiKey();
            $x_api_token    = Mage::helper('juniormaia_paymee')->getApiToken();

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "x-api-key: $x_api_key",
                    "x-api-token: $x_api_token"
                ),
            ));

            $response   = curl_exec($curl);
            $err        = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $cURLErrorMessage = "PayMee - cURL Error #:" . $err;
                return array(
                    "success" => false,
                    "response_payload" => $response,
                    "message" => $cURLErrorMessage
                );
            }

            $paymee_response = json_decode($response, true);
            if($paymee_response['status'] !== 0) {
                return array(
                    "success" => false,
                    "response_payload" => $response,
                    "message" => $paymee_response['errors'][0]['message']
                );
            }

            return array(
                "success" => true,
                "response_payload" => $response,
                "message" => "success"
            );
        } catch(Exception $e) {
            return array(
                "success" => false,
                "message" => $e->getMessage()
            );
        }
    }

    public function refundOrder($uuid, $amount) {
        try {

            Mage::helper('juniormaia_paymee')->logs(" ----- Chamando API ------");

            $url = 'https://api.paymee.com.br/';
            if (Mage::helper('juniormaia_paymee')->getEnvironmentSandbox()) {
                $url = 'https://apisandbox.paymee.com.br/';
            }

            $url            = $url."v1.1/transactions/{$uuid}/refund";
            $x_api_key      = Mage::helper('juniormaia_paymee')->getApiKey();
            $x_api_token    = Mage::helper('juniormaia_paymee')->getApiToken();

            $data = array(
                "amount" => $amount,
                "reason" => "Magento Mass Refund"
            );

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode($data, true),
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "x-api-key: $x_api_key",
                    "x-api-token: $x_api_token"
                ),
            ));

            $response   = curl_exec($curl);
            $err        = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $cURLErrorMessage = "PayMee - cURL Error #:" . $err;
                return array(
                    "success" => false,
                    "response_payload" => $response,
                    "message" => $cURLErrorMessage
                );
            }

            $paymee_response = json_decode($response, true);
            if($paymee_response['status'] !== 0) {
                return array(
                    "success" => false,
                    "response_payload" => $response,
                    "message" => $paymee_response['errors'][0]['message']
                );
            }

            return array(
                "success" => true,
                "response_payload" => $response,
                "message" => "success"
            );
        } catch(Exception $e) {
            return array(
                "success" => false,
                "message" => $e->getMessage()
            );
        }
    }

    public function sendDocuments($docType, $filePath, $proposal_id) {
        try {
            Mage::helper('juniormaia_paymee')->logs(" ----- Chamando API Documents ------");
            Mage::helper('juniormaia_paymee')->logs("type: {$docType}");
            Mage::helper('juniormaia_paymee')->logs("proposal: {$proposal_id}");
            Mage::helper('juniormaia_paymee')->logs("image: {$filePath}");

            $url = 'https://api.paymee.com.br/';
            if (Mage::helper('juniormaia_paymee')->getEnvironmentSandbox()) {
                $url = 'https://apisandbox.paymee.com.br/';
            }
            if ($docType == 'selfie') {
                $url = $url."v1.1/loans/upload/selfie";
            } else {
                $url = $url."v1.1/loans/upload/document";
            }

            $x_api_key      = Mage::helper('juniormaia_paymee')->getApiKey();
            $x_api_token    = Mage::helper('juniormaia_paymee')->getApiToken();

            if (function_exists('curl_file_create')) { // php 5.5+
                $cFile = curl_file_create($filePath);
            } else { //
                $cFile = '@' . realpath($filePath);
            }

            $postfield = array(
                'file'          => $cFile,
                'proposal_id'  => $proposal_id,
                'document'     => 'DOC_IDENTIDADE_FRENTE'
            );
            if ($docType == 'selfie') {
                unset($postfield['document']);
            }

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postfield,
                CURLOPT_HTTPHEADER => array(
                    "Accept:application/json",
                    "Content-Type: multipart/form-data",
                    "x-api-key: $x_api_key",
                    "x-api-token: $x_api_token"
                ),
            ));

            $response   = curl_exec($curl);
            $err        = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $cURLErrorMessage = "PayMee - cURL Error #:" . $err;
                return array(
                    "success" => false,
                    "message" => $cURLErrorMessage
                );
            }

            return json_decode($response, true);
        }
        catch(Exception $e) {
            return array(
                "success" => false,
                "response_payload" => $response,
                "message" => $e->getMessage()
            );
        }
    }
}