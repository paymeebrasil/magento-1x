<?php

class JuniorMaia_Paymee_Model_Standard extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'juniormaia_paymee';
    protected $_formBlockType = 'juniormaia_paymee/form_paymee';
    protected $_infoBlockType = 'juniormaia_paymee/info_paymee';

    public function assignData($data)
    {
        $info = $this->getInfoInstance();

        if ($data->getPaymeeCpf())
        {
            $info->setPaymeeCpf($data->getPaymeeCpf());
        }

        if ($data->getPaymeeBanco())
        {
            $info->setPaymeeBanco($data->getPaymeeBanco());
        }

        if ($data->getPaymeeBranch())
        {
            $info->setPaymeeBranch($data->getPaymeeBranch());
        }

        if ($data->getPaymeeAccount())
        {
            $info->setPaymeeAccount($data->getPaymeeAccount());
        }

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