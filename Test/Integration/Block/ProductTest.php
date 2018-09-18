<?php

namespace MageSuite\DailyDeal\Test\Integration\Block;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\DailyDeal\Block\Product
     */
    protected $productBlock;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->coreRegistry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productBlock = $this->objectManager->get(\MageSuite\DailyDeal\Block\Product::class);

        $priceRender = $this->objectManager->get(\Magento\Framework\View\LayoutInterface::class)->getBlock('product.price.render.default');

        if (!$priceRender) {
            $this->objectManager->get(\Magento\Framework\View\LayoutInterface::class)->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                    ],
                ]
            );
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
    public function testItReturnCorrectData()
    {
        $product = $this->productRepository->get('active_offer');
        $this->coreRegistry->register('product', $product);

        $offerData = $this->productBlock->getOfferData();

        $this->assertArrayHasKey('deal', $offerData);

        $this->assertTrue($offerData['deal']);

        $this->assertEquals(50, $offerData['items']);
        $this->assertEquals(1521417600, $offerData['from']);
        $this->assertEquals(1931932800, $offerData['to']);
        $this->assertEquals('5.0000', $offerData['price']);
        $this->assertEquals('none', $offerData['displayType']);

        $this->assertEquals(30, $offerData['oldDiscount']);

        $this->assertContains('$10.00', $offerData['oldPriceHtmlOnTile']);
        $this->assertNotContains('$5.00', $offerData['oldPriceHtmlOnTile']);
        $this->assertContains('$10.00', $offerData['oldPriceHtmlOnPdp']);
        $this->assertNotContains('$5.00', $offerData['oldPriceHtmlOnPdp']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store daily_deal/general/active 1
     */
    public function testItReturnsFalseWhenNoCurrentProductIsRegistered()
    {
        $this->coreRegistry->register('product', null);

        $offerData = $this->productBlock->getOfferData();

        $this->assertFalse($offerData);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 0
     */
    public function testItReturnsFalseIfDailyDealIsNotActive()
    {
        $product = $this->productRepository->get('active_offer');

        $this->coreRegistry->register('product', $product);

        $offerData = $this->productBlock->getOfferData();

        $this->assertFalse($offerData);
    }

    public static function loadProducts()
    {
        require __DIR__ . '/../_files/products.php';
    }

    public static function loadProductsRollback()
    {
        require __DIR__ . '/../_files/products_rollback.php';
    }
}