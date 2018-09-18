<?php

namespace MageSuite\DailyDeal\Service;

class CacheCleaner
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\DailyDeal\Block\Product
     */
    protected $productBlock;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Cache\Type\FrontendPool
     */
    protected $cachePool;

    /**
     * @var array
     */
    private $cacheList;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    private $pageCacheConfig;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $purgeCache;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MageSuite\DailyDeal\Block\Product $productBlock,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\Type\FrontendPool $cachePool,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\PageCache\Model\Config $pageCacheConfig,
        \Magento\CacheInvalidate\Model\PurgeCache $purgeCache,
        array $cacheList
    ) {
        $this->productRepository = $productRepository;
        $this->productBlock = $productBlock;
        $this->cache = $cache;
        $this->cachePool = $cachePool;
        $this->cacheState = $cacheState;
        $this->cacheList = $cacheList;
        $this->pageCacheConfig = $pageCacheConfig;
        $this->purgeCache = $purgeCache;
    }

    public function refreshProductCache($productId)
    {
        $product = $this->productRepository->getById($productId);

        if(!$product){
            return false;
        }

        $blockCacheTag = $this->productBlock->getCacheTag($productId);

        $this->cache->remove($blockCacheTag);

        $tags = array_merge($product->getIdentities(), [$blockCacheTag, 'virtual_category']);

        if (empty($tags)) {
            return false;
        }

        foreach ($this->cacheList as $cacheType) {
            if ($this->cacheState->isEnabled($cacheType)) {
                $this->cachePool->get($cacheType)->clean(
                    \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                    array_unique($tags)
                );
            }
        }

        $this->purgeCacheByTags(implode('|', $tags));

        return true;
    }

    private function purgeCacheByTags($tags)
    {
        $cacheType = $this->pageCacheConfig->getType();
        $isFpcEnabled = $this->pageCacheConfig->isEnabled();

        if ($cacheType == \Magento\PageCache\Model\Config::VARNISH and $isFpcEnabled === true) {
            $this->purgeCache->sendPurgeRequest($tags);
        }
    }
}