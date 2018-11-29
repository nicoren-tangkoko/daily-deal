<?php

namespace MageSuite\DailyDeal\Plugin\Smile\ElasticsuiteCatalog\Model\Product\Indexer\Fulltext\Datasource\PriceData\PriceConfigurable;

class UpdatePriceIndex
{
    protected $price = null;

    /**
     * @var \MageSuite\DailyDeal\Service\OfferManager
     */
    protected $offerManager;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    public function __construct(
        \MageSuite\DailyDeal\Service\OfferManager $offerManager,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ){
        $this->offerManager = $offerManager;
        $this->productRepository = $productRepository;
    }

    public function afterGetPrice($subject, $result, $priceData)
    {
        $price = $this->getProductPrice($priceData['entity_id']);

        return $price ? $price : $result;
    }

    public function afterGetOriginalPrice($subject, $result, $priceData)
    {
        $price = $this->getProductPrice($priceData['entity_id']);

        return $price ? $price : $result;
    }

    protected function getProductPrice($productId)
    {
        if (!$this->price) {
            $product = $this->productRepository->getById($productId);
            $this->price = $this->offerManager->getOfferPrice($product);
        }

        return $this->price;
    }
}
