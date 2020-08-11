<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Setup;

use Magento\Framework\DB\Ddl\Table as Table;
use GBPrimePay\Payments\Helper\Constant as Constant;

/**
 * DB setup script for TokenBase
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * DB setup code
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {


        $_prefix = Constant::TABLE_PREFIX;
        $setup->startSetup();


        $table = $setup->getConnection()->newTable(
            $setup->getTable($_prefix . 'stored_card')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'magento_customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Magento Customer ID'
        )->addColumn(
            'credit_card_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Credit Card Name'
        )->addColumn(
            'tokenid',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Credit Card ID'
        )->addColumn(
            'expiry_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Credit Card Name'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['default' => Table::TIMESTAMP_INIT],
            'Create Time'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [
                'nullable' => false,
                'default' => 'active'
            ],
            'Card Status'
        )->setComment(
            'Stored Cards for GBPrimePay payment methods'
        );


        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable($_prefix . 'customer')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'ID'
        )->addColumn(
            'magento_customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Magento Customer ID'
        )->addColumn(
            'gbprimepay_customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'GBPrimePay Customer ID'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Customer Email'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['default' => Table::TIMESTAMP_INIT],
            'Create Time'
        )->setComment(
            'Customer for GBPrimePay payment methods'
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable($_prefix . 'purchase')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'magento_customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Magento Customer ID'
        )->addColumn(
            'purchase_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Purchase Method'
        )->addColumn(
            'quoteid',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Quote ID'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['default' => Table::TIMESTAMP_INIT],
            'Create Time'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [
                'nullable' => false,
                'default' => 'active'
            ],
            'Purchase Status'
        )->setComment(
            'Purchase Order for GBPrimePay payment methods'
        );


        $setup->getConnection()->createTable($table);
    }
}
