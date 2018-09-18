<?php

namespace MageSuite\DailyDeal\Service;

class OfferManager implements \MageSuite\DailyDeal\Service\OfferManagerInterface
{
    const ITEM_OPTION_DD_OFFER = 'is_daily_deal';

    const TYPE_ADD = 1;
    const TYPE_REMOVE = 0;

    private $timestamp;
    private $storeId;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageSuite\DailyDeal\Helper\Configuration
     */
    protected $configuration; 
    
    /**
     * @var \MageSuite\DailyDeal\Helper\OfferData
     */
    protected $offerData;

    /**
     * @var \MageSuite\DailyDeal\Model\ResourceModel\Offer
     */
    protected $offerResource;

    /**
     * @var \MageSuite\DailyDeal\Service\CacheCleaner
     */
    protected $cacheCleaner;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockInterface;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\DailyDeal\Helper\Configuration $configuration,
        \MageSuite\DailyDeal\Helper\OfferData $offerData,
        \MageSuite\DailyDeal\Model\ResourceModel\Offer $offerResource,
        \MageSuite\DailyDeal\Service\CacheCleaner $cacheCleaner,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stockInterface
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->totalsCollector = $totalsCollector;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->offerData = $offerData;
        $this->offerResource = $offerResource;
        $this->cacheCleaner = $cacheCleaner;
        $this->indexerFactory = $indexerFactory;
        $this->stockInterface = $stockInterface;
    }

    public function refreshOffers($storeId = null)
    {
        $this->setStoreId($storeId);

        $offers = $this->getOffers();

        if(empty($offers)){
            return false;
        }

        $isQtyLimitationEnabled = $this->configuration->isQtyLimitationEnabled();

        foreach($offers as $offer){
            $action = $this->getOfferAction($offer, $isQtyLimitationEnabled);

            if($action === null){
                continue;
            }

            $this->applyAction($offer['entity_id'], $action);
        }

        return true;
    }

    public function getOffers()
    {
        $this->timestamp = $this->dateTime->timestamp();

        $items = $this->offerResource->getOffersByParameters($this->timestamp, $this->storeId);

        foreach($items as $key => $item){
            $items[$key]['daily_deal_limit'] = $this->offerResource->getAttributeValue($item['entity_id'], 'daily_deal_limit', $this->storeManager->getStore()->getId());
        }

        return $items;
    }

    public function getOfferAction($offer, $isQtyLimitationEnabled)
    {
        $from = $offer['daily_deal_from'] ? strtotime($offer['daily_deal_from']) : null;
        $to = $offer['daily_deal_to'] ? strtotime($offer['daily_deal_to']) : null;
        $productQty = $this->stockInterface->getStockQty($offer['entity_id']);

        if($offer['daily_deal_enabled']){

            if($productQty !== null and $productQty < 1){
                return self::TYPE_REMOVE;
            }

            if(!$from or !$to){
                return self::TYPE_REMOVE;
            }

            if($from > $this->timestamp or $to < $this->timestamp){
                return self::TYPE_REMOVE;
            }

            if($isQtyLimitationEnabled and $offer['daily_deal_limit'] !== null and (float)$offer['daily_deal_limit'] == 0){
                return self::TYPE_REMOVE;
            }

        }else{

            if($productQty !== null and $productQty < 1){
                return null;
            }

            if(!$from or !$to){
                return null;
            }

            if($isQtyLimitationEnabled and $offer['daily_deal_limit'] !== null and (float)$offer['daily_deal_limit'] == 0){
                return null;
            }

            if($from < $this->timestamp and $to > $this->timestamp){
                return self::TYPE_ADD;
            }
        }

        return null;
    }

    public function applyAction($productId, $action)
    {
        $this->offerResource->setAttributeValue($productId, 'daily_deal_enabled', $action, $this->storeId);

        if($action == self::TYPE_REMOVE){
            $this->removeProductFromQuotes($productId);
        }

        $this->refreshProductIndex($productId);
        $this->refreshProductCache($productId);
    }

    private function removeProductFromQuotes($productId)
    {
        $items = $this->offerResource->getItemsByProductId($productId);

        if(empty($items)){
            return true;
        }

        foreach($items as $quoteId => $item){
            $quote = $this->quoteRepository->get($quoteId);

            foreach($quote->getItems() as $quoteItem){
                if($quoteItem->getId() != $item['item_id']){
                    continue;
                }

                $quote->deleteItem($quoteItem);
            }

            $this->totalsCollector->collect($quote);
            $this->quoteRepository->save($quote->collectTotals());
        }

        return true;
    }

    public function refreshProductIndex($productId)
    {
        $indexes = ['catalogsearch_fulltext', 'catalog_product_price'];

        foreach($indexes as $indexId) {
            /** @var \Magento\Indexer\Model\Indexer $indexer */
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexId);
            $indexer->reindexRow($productId);
        }
    }

    public function refreshProductCache($productId)
    {
        $this->cacheCleaner->refreshProductCache($productId);
    }

    public function getOfferPrice($productId)
    {
        $this->setStoreId(null);

        if(!$this->offerData->isOfferEnabled($productId)){
            return null;
        }

        $price = $this->offerResource->getAttributeValue($productId, 'daily_deal_price', $this->storeManager->getStore()->getId());

        return $price;
    }

    public function getOfferLimit($productId)
    {
        if(!$this->offerData->isOfferEnabled($productId)){
            return null;
        }

        $limit = $this->offerResource->getAttributeValue($productId, 'daily_deal_limit', $this->storeManager->getStore()->getId());

        return $limit;
    }

    public function getProductParentId($productId)
    {
        return $this->offerResource->getProductParentId($productId);
    }

    public function getProductQtyInCart($productId, $quoteId)
    {
        return $this->offerResource->getProductQtyInCart($productId, $quoteId);
    }

    public function validateOfferInQuote($productId, $qty)
    {
        if(!$qty){
            return false;
        }

        if(!$this->offerData->isOfferEnabled($productId)){
            return false;
        }

        $isQtyLimitationEnabled = $this->configuration->isQtyLimitationEnabled();

        if(!$isQtyLimitationEnabled){
            return true;
        }

        $limit = $this->offerResource->getAttributeValue($productId, 'daily_deal_limit', $this->storeManager->getStore()->getId());

        return (float)$qty <= (float)$limit ? true : false;
    }

    /**
     * @param $productId
     * @param null $qty
     * @return bool
     */
    public function decreaseOfferLimit($productId, $qty = null)
    {
        if(!$this->offerData->isOfferEnabled($productId)){
            return true;
        }

        if(!$this->configuration->isQtyLimitationEnabled()){
            return true;
        }

        $qty = $qty ? $qty : 1;
        $storeId = $this->storeManager->getStore()->getId();

        $currentValue = $this->offerResource->getAttributeValue($productId, 'daily_deal_limit', $storeId);

        $newValue = max(0, $currentValue - $qty);

        $this->offerResource->setAttributeValue($productId, 'daily_deal_limit', $newValue, $storeId);

        if($newValue == 0){
            $this->applyAction($productId, self::TYPE_REMOVE);
        }

        $this->refreshProductIndex($productId);
        $this->refreshProductCache($productId);

        return true;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId !== null
            ? $storeId
            : $this->storeManager->getStore()->getId();
    }

}