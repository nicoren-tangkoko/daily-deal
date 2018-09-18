<?php
namespace MageSuite\DailyDeal\Cron;

class DailyDealRefresh
{
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
        \MageSuite\DailyDeal\Helper\Configuration $configuration,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\DailyDeal\Service\OfferManagerInterface $offerManager
    )
    {
        $this->configuration = $configuration;
        $this->storeManager = $storeManager;
        $this->offerManager = $offerManager;
    }

    public function execute()
    {
        $isActive = $this->configuration->isActive();

        if(!$isActive){
            return false;
        }

        $stores = $this->storeManager->getStores(true);

        $storeIds = array_keys($stores);
        sort($storeIds);

        foreach($storeIds as $storeId){
            $this->offerManager->refreshOffers($storeId);
        }

        return true;
    }
}