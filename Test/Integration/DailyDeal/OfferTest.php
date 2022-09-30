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

    public function setUp(): void
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
        $product = $this->productRepository->get('actual_offer');
        $offerKey = \MageSuite\DailyDeal\Service\OfferManager::ITEM_OPTION_DD_OFFER;

        $this->cart->addProduct($product, []);

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

        $offerPrice = $this->offerManager->getOfferPrice($product);

        $this->assertEquals(10, $offerPrice);
        $this->assertEquals(5, $product->getSpecialPrice());

        $this->cart->addProduct($product, []);

        $items = $this->cart->getQuote()->getAllItems();

        foreach ($items AS $item) {
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
    public function testItAddsRelatedProductWithSpecialPrice()
    {
        $product = $this->productRepository->get('actual_offer');
        $relatedProduct = $this->productRepository->get('smaller_qty');

        $this->cart->addProduct($product, []);
        $this->cart->addProductsByIds([$relatedProduct->getId()]);

        $items = $this->cart->getQuote()->getAllItems();

        $productItem = $this->cart->getQuote()->getItemByProduct($product);
        $relatedProductItem = $this->cart->getQuote()->getItemByProduct($relatedProduct);

        $this->assertEquals(20, $productItem->getProduct()->getPrice());
        $this->assertEquals(5, $productItem->getCustomPrice());

        $this->assertEquals(20, $relatedProductItem->getProduct()->getPrice());
        $this->assertEquals(5, $relatedProductItem->getCustomPrice());
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
        $product = $this->productRepository->get('actual_offer');

        $this->assertEquals(20, $product->getDailyDealLimit());

        $this->offerManager->decreaseOfferLimit($product, 15);

        $product = $this->productRepository->get('actual_offer');

        $this->assertEquals(5, $product->getDailyDealLimit());
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
        $qty = 2;

        $product = $this->productRepository->get('actual_offer');
        $this->assertEquals(20, $product->getDailyDealLimit());

        $quote = $this->prepareQuote($product, $qty);
        $this->quoteManagement->submit($quote);

        $product = $this->productRepository->get('actual_offer');
        $this->assertEquals(18, $product->getDailyDealLimit());
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
        $storeId = 1;

        $product = $this->productRepository->get('actual_offer');
        $this->assertEquals(20, $product->getDailyDealLimit());

        $this->cart->addProduct($product, ['qty' => 25]);

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

    private function prepareQuote($product, $qty)
    {
        $this->cart->addProduct($product, ['qty' => $qty]);

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
