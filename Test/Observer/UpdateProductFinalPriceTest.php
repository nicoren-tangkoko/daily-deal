<?php

namespace MageSuite\DailyDeal\Test\Integration\Observer;

class UpdateProductFinalPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\DailyDeal\Service\OfferManager
     */
    protected $offerManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->offerManager = $this->objectManager->get(\MageSuite\DailyDeal\Service\OfferManager::class);
        $this->productCollectionFactory = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Model\ProductRepository::class);

        parent::setUp();
    }

    /**
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItSetsCorrectFinalPrice()
    {
        $productId = 603;

        $product = $this->productRepository->getById($productId);

        $this->assertEquals(10, $product->getFinalPrice());

        $this->offerManager->applyAction(603, \MageSuite\DailyDeal\Service\OfferManager::TYPE_ADD);

        $productCollection = $this->productCollectionFactory
            ->create()
            ->addFieldToFilter('entity_id', 603)
            ->addFinalPrice();

        $firstProduct = $productCollection->getFirstItem();

        $this->assertEquals(3, $firstProduct->getFinalPrice());
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