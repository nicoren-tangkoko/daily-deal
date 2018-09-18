<?php

namespace MageSuite\DailyDeal\Pricing\Render;


class FinalPriceWithoutDailyDealBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    public function getPriceType($priceCode)
    {
        if($priceCode == \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE){
            $priceCode = \MageSuite\DailyDeal\Pricing\Price\FinalPriceWithoutDailyDeal::PRICE_CODE;
        }

        return $this->saleableItem->getPriceInfo()->getPrice($priceCode);
    }
}
