<?php

namespace MageSuite\DailyDeal\Test\Integration\DailyDeal;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class OfferTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \MageSuite\DailyDeal\Service\OfferManager
     */
    protected $offerManager;

    /**
     * @var \MageSuite\DailyDeal\Model\ResourceModel\Offer
     */
    protected $offerResource;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->cart = $this->objectManager->get(\Magento\Checkout\Model\Cart::class);
        $this->offerManager = $this->objectManager->get(\MageSuite\DailyDeal\Service\OfferManager::class);
        $this->offerResource = $this->objectManager->get(\MageSuite\DailyDeal\Model\ResourceModel\Offer::class);
        $this->quoteManagement = $this->objectManager->get(\Magento\Quote\Model\QuoteManagement::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItAddsProductWithCorrectValues()
    {
        $productId = 604;
        $offerKey = \MageSuite\DailyDeal\Service\OfferManager::ITEM_OPTION_DD_OFFER;

        $this->cart->addProduct($productId, []);

        $items = $this->cart->getQuote()->getAllItems();

        foreach($items AS $item){

            $this->assertEquals(20, $item->getProduct()->getPrice());
            $this->assertEquals(5, $item->getCustomPrice());

            $option = $item->getOptionByCode(
                $offerKey
            );

            $this->assertNotNull($option);
            $this->assertTrue((boolean)$option->getValue());

            $buyRequest = $item->getOptionByCode('info_buyRequest');

            $this->assertArrayHasKey($offerKey, $buyRequest);
            $this->assertTrue($buyRequest[$offerKey]);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItAddProductWithSpecialPrice()
    {
        $product = $this->productRepository->get('offer_with_special_price');

        $offerPrice = $this->offerManager->getOfferPrice($product->getId());

        $this->assertEquals(10, $offerPrice);
        $this->assertEquals(5, $product->getSpecialPrice());

        $this->cart->addProduct($product->getId(), []);

        $items = $this->cart->getQuote()->getAllItems();

        foreach($items AS $item){

            $this->assertEquals(20, $item->getProduct()->getPrice());
            $this->assertEquals(5, $item->getCustomPrice());
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItDecreaseOfferUsage()
    {
        $productId = 604;
        $storeId = 1;

        $this->assertEquals(20, $this->offerResource->getAttributeValue($productId, 'daily_deal_limit', $storeId));

        $this->offerManager->decreaseOfferLimit($productId, 15);

        $this->assertEquals(5, $this->offerResource->getAttributeValue($productId, 'daily_deal_limit', $storeId));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItDecreaseOfferUsageAfterCreateOrder()
    {
        $this->markTestSkipped();

        $productId = 604;
        $qty = 2;
        $storeId = 1;

        $this->assertEquals(20, $this->offerResource->getAttributeValue($productId, 'daily_deal_limit', $storeId));

        $quote = $this->prepareQuote($productId, $qty);
        $this->quoteManagement->submit($quote);

        $this->assertEquals(18, $this->offerResource->getAttributeValue($productId, 'daily_deal_limit', $storeId));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItLimitProductQtyInCart()
    {
        $productId = 604;
        $storeId = 1;

        $this->assertEquals(20, $this->offerResource->getAttributeValue($productId, 'daily_deal_limit', $storeId));

        $this->cart->addProduct($productId, ['qty' => 25]);

        $items = $this->cart->getQuote()->getAllItems();

        $itemsQty = [];

        foreach($items AS $item){
            $itemsQty[] = $item->getQty();
        }

        $this->assertEquals([20, 5], $itemsQty);
    }

    public static function loadProducts()
    {
        require __DIR__ . '/../_files/products.php';
    }

    public static function loadProductsRollback()
    {
        require __DIR__ . '/../_files/products_rollback.php';
    }

    private function prepareQuote($productId, $qty)
    {
        $this->cart->addProduct($productId, ['qty' => $qty]);

        $addressData = [
            'region' => 'CA',
            'postcode' => '11111',
            'lastname' => 'lastname',
            'firstname' => 'firstname',
            'street' => 'street',
            'city' => 'Los Angeles',
            'email' => 'admin@example.com',
            'telephone' => '11111111',
            'country_id' => 'US'
        ];

        $shippingMethod = 'freeshipping_freeshipping';

        $billingAddress = $this->objectManager->create('Magento\Quote\Api\Data\AddressInterface', ['data' => $addressData]);
        $billingAddress->setAddressType('billing');

        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');

        $rate = $this->objectManager->create(\Magento\Quote\Model\Quote\Address\Rate::class);
        $rate->setCode($shippingMethod);

        $shippingAddress->setShippingMethod($shippingMethod);
        $shippingAddress->setShippingRate($rate);

        $quote = $this->cart->getQuote();
        $quote->setBillingAddress($billingAddress);
        $quote->setShippingAddress($shippingAddress);
        $quote->getShippingAddress()->addShippingRate($rate);

        $payment = $quote->getPayment();
        $payment->setMethod('checkmo');
        $quote->setPayment($payment);

        $quote->setCustomerEmail('test@example.com');
        $quote->setCustomerIsGuest(true);

        $quote->collectTotals();

        return $quote;
    }
}