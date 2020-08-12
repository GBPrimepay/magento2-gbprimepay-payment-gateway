<?php
/**
 * GBPrimePay_Payments extension
 * @package GBPrimePay_Payments
 * @copyright Copyright (c) 2020 GBPrimePay Payments (https://gbprimepay.com/)
 */

namespace GBPrimePay\Payments\Setup;


use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table as Table;
use GBPrimePay\Payments\Helper\Constant as Constant;

/**
 * DB setup script for TokenBase
 */
class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * DB setup code
     *
     * @param \Magento\Framework\Setup\UpgradeSchemaInterface $upgrade
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function upgrade(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {


        $_prefix = Constant::TABLE_PREFIX;
        $version = $context->getVersion();
        $setup->startSetup();

        if (version_compare($version, '1.8.4') < 0) {
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
        }


        $setup->getConnection()->createTable($table);
    }
}
