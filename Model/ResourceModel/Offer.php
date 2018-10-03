<?php

namespace MageSuite\DailyDeal\Model\ResourceModel;

class Offer extends \Magento\Catalog\Model\ResourceModel\AbstractResource
{
    const DEFAULT_STORE_ID = 0;

    private $attributes = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $configurableModel;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableModel,
        \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resource = $resource;
        $this->configurableModel = $configurableModel;
        $this->productRepository = $productRepository;
    }

    public function getOffersByParameters($timestamp, $storeId)
    {
        $productsCollection = $this->productCollectionFactory->create();

        $productsCollection
            ->setStoreId($storeId)
            ->addAttributeToSelect('daily_deal_limit')
            ->addFieldToFilter([
                ['attribute' => 'daily_deal_from', '<=' => $timestamp],
                ['attribute' => 'daily_deal_to', '>=' => $timestamp],
                ['attribute' => 'daily_deal_enabled', '=' => 1]
            ]);

        return $productsCollection->getItems();
    }

    public function getItemsByProductId($productId)
    {
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                ['qo' => $this->resource->getTableName('quote_item_option')],
                ['q.entity_id', 'qo.item_id']
            )
            ->joinLeft(['qi' => $this->resource->getTableName('quote_item')], 'qi.item_id = qo.item_id', '')
            ->joinLeft(['q' => $this->resource->getTableName('quote')], 'q.entity_id = qi.quote_id', '')

            ->where('q.is_active = ?', 1)
            ->where('qo.product_id = ?', $productId)
            ->where('qo.code = ?', \MageSuite\DailyDeal\Service\OfferManager::ITEM_OPTION_DD_OFFER)
            ->where('qo.value = ?', 'true');

        return $this->resource->getConnection()->fetchAssoc($select);
    }

    public function getParentProduct($product)
    {
        $productIds = $this->configurableModel->getParentIdsByChild($product->getId());

        if(empty($productIds)){
            return false;
        }

        return $this->productRepository->getById($productIds[0]);
    }

    public function getProductQtyInCart($productId, $quoteId)
    {
        $table = 'quote_item';

        $select = $this->resource->getConnection()
            ->select()
            ->from(
                ['qi' => $this->resource->getTableName($table)],
                ['qi.qty']
            )
            ->where('qi.product_id = ?', $productId)
            ->where('qi.quote_id = ?', $quoteId);

        $result = $this->resource->getConnection()->fetchCol($select);

        if(empty($result)){
            return 0;
        }

        $productQty = 0;
        foreach($result as $itemQty){
            $productQty += $itemQty;
        }

        return $productQty;
    }


}