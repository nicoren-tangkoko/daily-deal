<?php

namespace MageSuite\DailyDeal\Plugin;

class DisableReorderingOffer
{

    public function aroundAddOrderItem(\Magento\Checkout\Model\Cart $subject, callable $proceed, $orderItem, $qtyFlag = null)
    {
        $buyRequest = $orderItem->getProductOptionByCode('info_buyRequest');

        $offerKey = \MageSuite\DailyDeal\Service\OfferManager::ITEM_OPTION_DD_OFFER;

        if(isset($buyRequest[$offerKey]) and $buyRequest[$offerKey]) {
            return $subject;
        }

        return $proceed($orderItem, $qtyFlag);
    }
}
