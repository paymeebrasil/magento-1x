<?php

class JuniorMaia_Paymee_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getBanks() {

        $banks = array(
            //array('code' => '0', 'name' => 'PIX',                                       'value' => 'PIX'),
            array('code' => '001', 'name' => '001 - Banco do Brasil S.A',               'value' => 'BB_TRANSFER'),
            array('code' => '237', 'name' => '237 - Banco Bradesco S.A',                'value' => 'BRADESCO_TRANSFER'),
            array('code' => '341', 'name' => '341 - Banco Itaú-Unibanco S.A ',          'value' => 'ITAU_TRANSFER_GENERIC'),
            array('code' => '341', 'name' => '341 - Depósito Identificado Itaú',         'value' => 'ITAU_DI'),
            array('code' => '104', 'name' => '104 - Caixa Econômica Federal',           'value' => 'CEF_TRANSFER'),
            array('code' => '202', 'name' => '202 - Banco Original S.A',                'value' => 'ORIGINAL_TRANSFER'),
            array('code' => '033', 'name' => '033 - Banco Santander S.A',               'value' => 'SANTANDER_TRANSFER'),
            array('code' => '033', 'name' => '033 - Banco Santander S.A (Depósito em dinheiro)',        'value' => 'SANTANDER_DI'),
            array('code' => '077', 'name' => '077 - Banco Inter S.A',                   'value' => 'INTER_TRANSFER'),
            array('code' => '077', 'name' => '077 - Banco Inter S.A (BS2)',             'value' => 'BS2_TRANSFER'),
            //array('code' => '0', 'name' => 'OUTROS BANCOS',                             'value' => 'OUTROS_BANCOS'),
        );

        return $banks;
    }

    public function getApiKey() {
        return Mage::getStoreConfig('payment/juniormaia_paymee/x_api_key');
    }

    public function getApiToken() {
        return Mage::getStoreConfig('payment/juniormaia_paymee/x_api_token');
    }

    public function getDiscriminator() {
        return Mage::getStoreConfig('payment/juniormaia_paymee/discriminator');
    }

    public function getEnvironmentSandbox() {
        if (Mage::getStoreConfig('payment/juniormaia_paymee/sandbox')){
            return true;
        } else{
            return false;
        }
    }

    public function getCommentOrder() {
        if (Mage::getStoreConfig('payment/juniormaia_paymee/order_comment')){
            return true;
        } else{
            return false;
        }
    }

    public function getMaxAge() {
        $max_age = Mage::getStoreConfig('payment/juniormaia_paymee/max_age');
        if (!isset($max_age) || $max_age < 5) {
            $max_age = 2880;
        }
        return $max_age;
    }

    public function logs($message) {
        if (Mage::getStoreConfig('payment/juniormaia_paymee/log')) {
            Mage::log($message, null, 'juniormaia_paymee.log', true);
        }
    }
}