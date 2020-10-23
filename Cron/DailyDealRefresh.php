<?php
namespace MageSuite\DailyDeal\Cron;

class DailyDealRefresh
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \MageSuite\DailyDeal\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageSuite\DailyDeal\Service\OfferManagerInterface
     */
    protected $offerManager;

    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \MageSuite\DailyDeal\Helper\Configuration $configuration,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\DailyDeal\Service\OfferManagerInterface $offerManager
    )
    {
        $this->cache = $cache;
        $this->configuration = $configuration;
        $this->storeManager = $storeManager;
        $this->offerManager = $offerManager;
    }

    public function execute()
    {
        $isActive = $this->configuration->isActive();

        if (!$isActive) {
            return false;
        }

        $countChangedOffers = 0;
        $stores = $this->storeManager->getStores(true);
        $storeIds = array_keys($stores);
        sort($storeIds);

        foreach ($storeIds as $storeId) {
            $countChangedOffers += $this->offerManager->refreshOffers($storeId);
        }

        if ($countChangedOffers > 0) {
            $this->cache->clean([\MageSuite\ContentConstructorFrontend\Model\Component\DailyDealTeaser::CACHE_TAG]);
        }

        return true;
    }
}
