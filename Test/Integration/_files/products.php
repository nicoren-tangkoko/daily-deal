<?php

use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$product = $objectManager->create('Magento\Catalog\Model\Product');

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(600)
    ->setAttributeSetId(4)
    ->setName('Active offer')
    ->setSku('active_offer')
    ->setUrlKey('active_offer')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setSpecialPrice(7)
    ->setDailyDealEnabled(1)
    ->setDailyDealLimit(50)
    ->setDailyDealInitialAmount(60)
    ->setDailyDealFrom('2018-03-19 00:00:00')
    ->setDailyDealTo('2031-03-22 08:00:00')
    ->setDailyDealPrice(5)
    ->save();

$product = $objectManager->create('Magento\Catalog\Model\Product');

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(601)
    ->setAttributeSetId(4)
    ->setName('Old offer')
    ->setSku('old_offer')
    ->setUrlKey('old_offer')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 2, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setDailyDealEnabled(1)
    ->setDailyDealLimit(20)
    ->setDailyDealFrom('2018-03-11 00:00:00')
    ->setDailyDealTo('2018-03-16 08:00:00')
    ->setDailyDealPrice(1)
    ->save();

$product = $objectManager->create('Magento\Catalog\Model\Product');

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(602)
    ->setAttributeSetId(4)
    ->setName('No offer')
    ->setSku('no_offer')
    ->setUrlKey('no_offer')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 2, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->save();

$product = $objectManager->create('Magento\Catalog\Model\Product');

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(603)
    ->setAttributeSetId(4)
    ->setName('New offer')
    ->setSku('new_offer')
    ->setUrlKey('new_offer')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 2, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setDailyDealEnabled(0)
    ->setDailyDealLimit(20)
    ->setDailyDealFrom('2018-03-14 00:00:00')
    ->setDailyDealTo('2031-03-25 08:00:00')
    ->setDailyDealPrice(3)
    ->save();

$product = $objectManager->create('Magento\Catalog\Model\Product');

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(604)
    ->setAttributeSetId(4)
    ->setName('Actual offer')
    ->setSku('actual_offer')
    ->setUrlKey('actual_offer')
    ->setPrice(20)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setDailyDealEnabled(1)
    ->setDailyDealLimit(20)
    ->setDailyDealFrom('2000-03-14 00:00:00')
    ->setDailyDealTo('2035-03-25 08:00:00')
    ->setDailyDealPrice(5)
    ->save();

$product = $objectManager->create('Magento\Catalog\Model\Product');

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(605)
    ->setAttributeSetId(4)
    ->setName('Offer with smaller qty')
    ->setSku('smaller_qty')
    ->setUrlKey('smaller_qty')
    ->setPrice(20)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 10, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setDailyDealEnabled(1)
    ->setDailyDealLimit(20)
    ->setDailyDealFrom('2000-03-14 00:00:00')
    ->setDailyDealTo('2035-03-25 08:00:00')
    ->setDailyDealPrice(5)
    ->save();

$product = $objectManager->create('Magento\Catalog\Model\Product');

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(606)
    ->setAttributeSetId(4)
    ->setName('Offer with special price')
    ->setSku('offer_with_special_price')
    ->setUrlKey('offer_with_special_price')
    ->setPrice(20)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 10, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setSpecialPrice(5)
    ->setDailyDealEnabled(1)
    ->setDailyDealLimit(20)
    ->setDailyDealFrom('2000-03-14 00:00:00')
    ->setDailyDealTo('2035-03-25 08:00:00')
    ->setDailyDealPrice(10)
    ->save();