<?php

namespace MageSuite\DailyDeal\Observer;

class DecreaseOfferUsage implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\DailyDeal\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\DailyDeal\Service\OfferManagerInterface
     */
    protected $offerManager;

    public function __construct(
        \MageSuite\DailyDeal\Helper\Configuration $configuration,
        \MageSuite\DailyDeal\Service\OfferManagerInterface $offerManager
    ) {
        $this->configuration = $configuration;
        $this->offerManager = $offerManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isQtyLimitationEnabled = $this->configuration->isQtyLimitationEnabled();

        if(!$isQtyLimitationEnabled){
            return $this;
        }

        $order = $observer->getEvent()->getOrder();

        foreach($order->getAllItems() as $item){
            if ($item->getParentItem()) {
                continue;
            }

            $buyRequest = $item->getProductOptionByCode('info_buyRequest');
            $offerKey = \MageSuite\DailyDeal\Service\OfferManager::ITEM_OPTION_DD_OFFER;

            if(!isset($buyRequest[$offerKey]) or !$buyRequest[$offerKey]) {
                continue;
            }

            $this->offerManager->decreaseOfferLimit($item->getProductId(), $item->getQtyOrdered());
        }

        return $this;
    }
}