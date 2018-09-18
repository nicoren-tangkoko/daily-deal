<?php

namespace MageSuite\DailyDeal\Test\Integration\Service;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class OfferManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\DailyDeal\Service\OfferManager
     */
    protected $offerManager;

    /**
     * @var \MageSuite\DailyDeal\Model\ResourceModel\Offer
     */
    protected $offerResource;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->offerManager = $this->objectManager->get(\MageSuite\DailyDeal\Service\OfferManager::class);
        $this->offerResource = $this->objectManager->get(\MageSuite\DailyDeal\Model\ResourceModel\Offer::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItReturnsCorrectOffers()
    {
        $offers = $this->offerManager->getOffers();

        $this->assertCount(6, $offers);
        $this->assertCount(12, $offers[0]);
        $this->assertEquals(600, $offers[0]['entity_id']);
        $this->assertEquals(601, $offers[1]['entity_id']);

        $this->assertEquals('2018-03-19 00:00:00', $offers[0]['daily_deal_from']);
        $this->assertEquals('2031-03-22 08:00:00', $offers[0]['daily_deal_to']);
        $this->assertEquals(20, $offers[1]['daily_deal_limit']);
        $this->assertEquals(1, $offers[1]['daily_deal_enabled']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoConfigFixture current_store daily_deal/general/active 1
     * @magentoConfigFixture current_store daily_deal/general/use_qty_limitation 1
     */
    public function testItReturnsCorrectData()
    {
        $date = new \DateTime('2018-03-20 01:00:00');
        $storeId = 1;
        $qtyLimitation = 1;

        $this->offerManager->setTimestamp($date->getTimestamp());
        $this->offerManager->setStoreId($storeId);

        $offers = $this->offerManager->getOffers();

        $this->assertNull($this->offerManager->getOfferAction($offers[0], $qtyLimitation));

        $this->assertEquals(
            \MageSuite\DailyDeal\Service\OfferManager::TYPE_REMOVE,
            $this->offerManager->getOfferAction($offers[1], $qtyLimitation));

        $this->assertEquals(
            \MageSuite\DailyDeal\Service\OfferManager::TYPE_ADD,
            $this->offerManager->getOfferAction($offers[2], $qtyLimitation));

        $product = $this->productRepository->getById($offers[1]['entity_id']);

        $this->assertEquals(1, $product->getDailyDealEnabled());
        $this->offerManager->applyAction($offers[1]['entity_id'], \MageSuite\DailyDeal\Service\OfferManager::TYPE_REMOVE);
        $this->assertEquals(0, $this->offerResource->getAttributeValue($offers[1]['entity_id'], 'daily_deal_enabled', $storeId));

        $product = $this->productRepository->getById($offers[2]['entity_id']);

        $this->assertEquals(0, $product->getDailyDealEnabled());
        $this->offerManager->applyAction($offers[2]['entity_id'], \MageSuite\DailyDeal\Service\OfferManager::TYPE_ADD);
        $this->assertEquals(1, $this->offerResource->getAttributeValue($offers[2]['entity_id'], 'daily_deal_enabled', $storeId));
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