<?php

class JuniorMaia_Paymee_Block_Form_Boleto extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('juniormaia/paymee/form/boleto.phtml');
    }
}