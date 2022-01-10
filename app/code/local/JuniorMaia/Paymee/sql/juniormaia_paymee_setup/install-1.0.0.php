<?php

$installer = $this;
$installer->startSetup();

/*
 * Reset tables
 */
try {
    $installer->run("
        ALTER TABLE `{$installer->getTable('sales/quote_payment')}` 
        ADD `paymee_uuid` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_status` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_message` text DEFAULT NULL,
        ADD `paymee_cpf` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_banco` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_branch` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_account` VARCHAR(255) DEFAULT NULL;
          
        ALTER TABLE `{$installer->getTable('sales/order_payment')}` 
        ADD `paymee_uuid` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_status` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_message` text DEFAULT NULL,
        ADD `paymee_cpf` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_banco` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_branch` VARCHAR(255) DEFAULT NULL,
        ADD `paymee_account` VARCHAR(255) DEFAULT NULL;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

$installer->endSetup();