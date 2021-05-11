<?php

class JuniorMaia_Paymee_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('cancel_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        // Append new mass action option
        $this->getMassactionBlock()->addItem(
            'cancelids',
            array('label' => $this->__('PayMee - Cancelar'),
                'url'   => $this->getUrl('adminhtml/paymee/cancel')
            )
        );
    }
}