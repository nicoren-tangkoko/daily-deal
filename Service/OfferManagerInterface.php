<?php

namespace MageSuite\DailyDeal\Service;

interface OfferManagerInterface
{
    public function getOffers();

    public function applyAction($productId, $action);

    public function refreshProductCache($productId);

    public function getOfferPrice($productId);

    public function getOfferLimit($productId);

    public function validateOfferInQuote($productId, $qty);

    public function decreaseOfferLimit($productId, $qty);

    public function getProductParentId($productId);
}