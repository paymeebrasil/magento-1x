<?php

class JuniorMaia_Paymee_Model_Pix extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'juniormaia_paymee_pix';
    protected $_formBlockType = 'juniormaia_paymee/form_pix';
    protected $_infoBlockType = 'juniormaia_paymee/info_paymee';

    public function assignData($data)
    {
        $info = $this->getInfoInstance();

        $info->setAdditionalInformation('paymee_cpf', $data->getPaymeeCpf());
        $info->setAdditionalInformation('paymee_banco', $data->getPaymeeBanco());
        $info->setAdditionalInformation('paymee_branch', $data->getPaymeeBranch());
        $info->setAdditionalInformation('paymee_account', $data->getPaymeeAccount());

        return $this;
    }

    public function validate()
    {
        parent::validate();
        $info = $this->getInfoInstance();
        return $this;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('paymee/checkout/payment', array('_secure' => true));
    }

}