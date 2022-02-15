<?php

class JuniorMaia_Paymee_Block_Info_Paymee extends Mage_Payment_Block_Info
{
    /**
     * Constructor method
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('juniormaia/paymee/info/paymee.phtml');
    }
}