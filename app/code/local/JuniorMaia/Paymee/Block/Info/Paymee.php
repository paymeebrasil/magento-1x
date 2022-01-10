<?php

class JuniorMaia_Paymee_Block_Info_Paymee extends Mage_Payment_Block_Info
{
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation)
        {
            return $this->_paymentSpecificInformation;
        }

        $data = array();
        if ($this->getInfo()->getCpf())
        {
            $data[Mage::helper('payment')->__('CPF')] = $this->getInfo()->getCpf();
        }

        if ($this->getInfo()->getBanco())
        {
            $data[Mage::helper('payment')->__('Banco')] = $this->getInfo()->getBanco();
        }

        if ($this->getInfo()->getBranch())
        {
            $data[Mage::helper('payment')->__('Branch')] = $this->getInfo()->getBranch();
        }

        if ($this->getInfo()->getAccount())
        {
            $data[Mage::helper('payment')->__('Account')] = $this->getInfo()->getAccount();
        }

        $transport = parent::_prepareSpecificInformation($transport);

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}