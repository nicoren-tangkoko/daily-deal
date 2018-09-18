<?php

namespace MageSuite\DailyDeal\Pricing\Price;

use Magento\Catalog\Model\Product;

class FinalPriceWithoutDailyDeal extends \Magento\Framework\Pricing\Price\AbstractPrice implements \Magento\Framework\Pricing\Price\BasePriceProviderInterface
{
    /**
     * Price type
     */
    const PRICE_CODE = 'final_price_without_daily_deal';

    protected $value;

    public function getValue()
    {
        $value = $this->getFinalPriceWithoutDailyDeal();

        if(!$value){
            return false;
        }

        return $value;
    }

    public function getFinalPriceWithoutDailyDeal()
    {
        if ($this->value === null) {
            $this->value = false;
            foreach ($this->priceInfo->getPrices() as $price) {

                if($this->isExcludedClass($price)){
                    continue;
                }

                if ($price->getValue() !== false) {
                    $this->value = min($price->getValue(), $this->value ?: $price->getValue());
                }
            }
        }

        return $this->value;
    }

    private function isExcludedClass($class)
    {
        if($class instanceof \Magento\Catalog\Pricing\Price\BasePrice){
            return true;
        }

        if($class instanceof \Magento\Catalog\Pricing\Price\FinalPrice){
            return true;
        }

        if($class instanceof \MageSuite\DailyDeal\Pricing\Price\OfferPrice){
            return true;
        }

        return false;
    }
}