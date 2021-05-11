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

    public function checkTransactionStatus($obj) {

        try {

            if($obj['order']->getStatus() !== 'pending') {
                return false;
            }

            $x_api_key      = Mage::helper('juniormaia_paymee')->getApiKey();
            $x_api_token    = Mage::helper('juniormaia_paymee')->getApiToken();

            $url = 'https://api.paymee.com.br/';
            if (Mage::helper('juniormaia_paymee')->getEnvironmentSandbox()) {
                $url = 'https://apisandbox.paymee.com.br/';
            }

            $url = $url."paymee.com.br/v1.1/transactions/" . $obj['payload']['saleToken'];

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

            return $responseData['situation'] === 'PAID';
        }
        catch(Exception $e) {
            print_r($e->getmessage());
            Mage::helper('juniormaia_paymee')->logs($e->getmessage());
            return false;
        }
    }
}