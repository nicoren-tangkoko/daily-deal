<?php

namespace MageSuite\DailyDeal\Pricing\Price;

class OfferPrice extends \Magento\Framework\Pricing\Price\AbstractPrice implements \Magento\Framework\Pricing\Price\BasePriceProviderInterface
{
    /**
     * Price type
     */
    const PRICE_CODE = 'offer_price';

    /**
     * @var \MageSuite\DailyDeal\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\DailyDeal\Service\OfferManagerInterface
     */
    protected $offerManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Pricing\SaleableInterface $saleableItem,
        $quantity,
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \MageSuite\DailyDeal\Helper\Configuration $configuration,
        \MageSuite\DailyDeal\Service\OfferManagerInterface $offerManager,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);

        $this->configuration = $configuration;
        $this->offerManager = $offerManager;
        $this->registry = $registry;
    }

    public function getValue()
    {
        $isActive = $this->configuration->isActive();

        if (!$isActive) {
            return false;
        }

        if ($this->value === null) {
            $price = $this->getOfferPrice();
            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
            $this->value = $priceInCurrentCurrency ? floatval($priceInCurrentCurrency) : false;
        }

        return $this->value;
    }

    public function getOfferPrice()
    {
        if (!$this->value) {

            if (!$this->product || !$this->product->getId()) {
                $this->value = false;
                return $this->value;
            }

            $offerPrice = $this->offerManager->getOfferPrice($this->product);
            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($offerPrice);

            $this->value = $priceInCurrentCurrency ? floatval($priceInCurrentCurrency) : false;
        }

        return $this->value;
    }
}
