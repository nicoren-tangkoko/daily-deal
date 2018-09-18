<?php

namespace MageSuite\DailyDeal\Plugin;

class DailyDealSaleOriginPlugin
{
    const DISCOUNT_TYPE_DAILY_DEAL = 'daily_deal';

    /**
     * @var \MageSuite\DailyDeal\Service\OfferManagerInterface
     */
    protected $offerManager;

    public function __construct(
        \MageSuite\DailyDeal\Service\OfferManagerInterface $offerManager
    ) {
        $this->offerManager = $offerManager;
    }

    public function aroundGetSaleOrigin(\MageSuite\Frontend\Helper\Product $subject, callable $proceed, $product)
    {
        $offerPrice = $this->offerManager->getOfferPrice($product->getId());

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