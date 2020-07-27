<?php

namespace MageSuite\DailyDeal\Service;

class SalableStockResolver
{
    protected $storeManager;
    protected $getProductSalableQty;
    protected $stockResolver;
    protected $logger;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $getProductSalableQty,
        \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->stockResolver = $stockResolver;
        $this->logger = $logger;
    }

    public function execute($productSku)
    {
        $websiteCode = $this->getWebsiteCode();
        if (!$websiteCode) {
            return null;
        }

        $stockId = $this->getStockId($websiteCode);
        if (!$stockId) {
            return null;
        }

        try {
            $salableQty = $this->getProductSalableQty->execute($productSku, $stockId);
        } catch (\Magento\Framework\Exception\InputException $inputException) {
            $salableQty = null;
            $this->logger->error($inputException->getMessage());

        } catch (\Magento\Framework\Exception\LocalizedException $localizedException) {
            $salableQty = null;
            $this->logger->error($localizedException->getMessage());
        }

        return $salableQty;
    }

    protected function getStockId($websiteCode)
    {
        try {
            $stockId = $this->stockResolver->execute(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $noSuchEntityException) {
            $stockId = null;
            $this->logger->error($noSuchEntityException->getMessage());
        }

        return $stockId;
    }

    protected function getWebsiteCode()
    {
        try {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
        } catch (\Magento\Framework\Exception\LocalizedException $localizedException) {
            $websiteCode = null;
            $this->logger->error($localizedException->getMessage());
        }

        return $websiteCode;
    }
}
