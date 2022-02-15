<?php

class JuniorMaia_Paymee_Model_Boleto extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'juniormaia_paymee_boleto';

    protected $_isGateway                   = true;
    protected $_canUseForMultishipping      = false;
    protected $_isInitializeNeeded          = true;
    protected $_canUseInternal              = true;

    protected $_formBlockType = 'juniormaia_paymee/form_boleto';
    protected $_infoBlockType = 'juniormaia_paymee/info_boleto';

    protected $_canOrder  = true;

    public function assignData($data)
    {
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('paymee_cpf', $data->getPaymeeCpf());
        $info->setAdditionalInformation('paymee_boleto_installments', $data->getPaymeeBoletoInstallments());

        if ($data->getProposalId())
        {
            $info->setAdditionalInformation('paymee_proposal_id', $data->getProposalId());
        }

        return $this;
    }

    public function initialize($paymentAction, $stateObject)
    {
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
        return Mage::getUrl('paymee/checkout/loansCreate', array('_secure' => true));
    }

}