<?php

namespace MageSuite\DailyDeal\Plugin;

class DailyDealSaleOriginPlugin
{
    const DISCOUNT_TYPE_DAILY_DEAL = 'daily_deal';

    /**
     * @var \MageSuite\DailyDeal\Service\OfferManagerInterface
     */
    protected $offerManager;
    /**
     * @var \MageSuite\DailyDeal\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\DailyDeal\Service\OfferManagerInterface $offerManager,
        \MageSuite\DailyDeal\Helper\Configuration $configuration
    ) {
        $this->offerManager = $offerManager;
        $this->configuration = $configuration;
    }

    public function aroundGetSaleOrigin(\MageSuite\Frontend\Helper\Product $subject, callable $proceed, $product)
    {
        if(!$this->configuration->isActive()){
            return $proceed($product);
        }

        $offerPrice = $this->offerManager->getOfferPrice($product);

        if(!$offerPrice){
            return $proceed($product);
        }

        $specialPrice = $product->getSpecialPrice();

        if (!$specialPrice || $specialPrice >= $offerPrice) {
            return self::DISCOUNT_TYPE_DAILY_DEAL;
        }

        return $proceed($product);

    }
}