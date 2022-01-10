<?php

try{

    Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
    $installer = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

    $installer->startSetup();

    $installer->addAttribute(
        "order",
        "paymee_uuid",
        array(
            "type"              => "varchar",
            'is_user_defined'    => false,
            'required'          => false,
            'searchable'        => true,
            'label'             => 'Paymee uuid',
            'visible'           => true
        )
    );

    $installer->addAttribute(
        "order",
        "paymee_salecode",
        array(
            "type"              => "varchar",
            'is_user_defined'    => false,
            'required'          => false,
            'searchable'        => true,
            'label'             => 'Paymee saleCode',
            'visible'           => true
        )
    );

    $installer->addAttribute(
        "order",
        "paymee_referencecode",
        array(
            "type"              => "varchar",
            'is_user_defined'    => false,
            'required'          => false,
            'searchable'        => true,
            'label'             => 'Paymee saleCode',
            'visible'           => true
        )
    );

    $installer->endSetup();

} catch (exception $e){
    Mage::log(print_r($e,true));
}