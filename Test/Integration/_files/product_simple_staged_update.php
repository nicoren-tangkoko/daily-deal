<?php

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$updateFactory = $objectManager->get(\Magento\Staging\Model\UpdateFactory::class);
$updateRepository = $objectManager->get(\Magento\Staging\Api\UpdateRepositoryInterface::class);
$productStaging = $objectManager->get(\Magento\CatalogStaging\Api\ProductStagingInterface::class);
$versionManager = $objectManager->get(\Magento\Staging\Model\VersionManager::class);
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var \Magento\Catalog\Model\Product $product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 1')
    ->setSku('simple')
    ->setPrice(10)
    ->setQty(100)
    ->setUrlKey('simple-' . rand(10, 1000))
    ->setDescription('Description with <b>html tag</b>')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setHasOptions(true);
$productRepository->save($product);

//Stage changes
$startTime = date('Y-m-d H:i:s', time());
$endTime = date('Y-m-d H:i:s', strtotime('+10 days'));
$updateData = [
    'name' => 'Product Update Test',
    'start_time' => $startTime,
    'end_time' => $endTime,
    'is_campaign' => 0,
    'is_rollback' => null,
];

$update = $updateFactory->create(['data' => $updateData]);
$updateRepository->save($update);
$product = $productRepository->get('simple');

$versionManager->setCurrentVersionId($update->getId());
$product->setName('Updated Product')->setPrice(5.99);
$productStaging->schedule($product, $update->getId());

sleep(1);

$product = $productRepository->get('simple');
$productRepository->save($product);
