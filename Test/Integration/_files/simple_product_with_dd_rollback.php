<?php

declare(strict_types=1);

$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    $product = $productRepository->get('simple', true);
    if ($product->getId()) {
        $productRepository->delete($product);
    }
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) { // phpcs:ignore
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
