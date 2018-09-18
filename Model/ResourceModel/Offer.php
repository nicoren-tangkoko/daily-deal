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

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableModel
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resource = $resource;
        $this->configurableModel = $configurableModel;
    }

    public function getOffersByParameters($timestamp, $storeId)
    {
        $productsCollection = $this->productCollectionFactory->create();

        $productsCollection
            ->addStoreFilter($storeId)
            ->addFieldToFilter([
                ['attribute' => 'daily_deal_from', '<=' => $timestamp],
                ['attribute' => 'daily_deal_to', '>=' => $timestamp],
                ['attribute' => 'daily_deal_enabled', '=' => 1]
            ]);

        $select = $productsCollection->getSelect();

        return $this->resource->getConnection()->fetchAll($select);
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

    public function getAttributeValue($productId, $attributeCode, $storeId, $specificStore = false)
    {
        $attributeData = $this->getAttributeDataByCode($attributeCode);
        $table = 'catalog_product_entity_' . $attributeData['backend_type'];

        $stores = $specificStore ? [$storeId] : [$storeId, self::DEFAULT_STORE_ID];

        $select = $this->resource->getConnection()
            ->select()
            ->from(
                ['c' => $this->resource->getTableName($table)],
                ['c.store_id', 'c.value']
            )
            ->where('c.entity_id = ?', $productId)
            ->where('c.attribute_id = ?', $attributeData['attribute_id'])
            ->where('c.store_id IN (?)', $stores);

        $result = $this->resource->getConnection()->fetchAssoc($select);

        foreach($stores as $storeId){
            if(isset($result[$storeId])){
                return $result[$storeId]['value'];
            }
        }

        return null;
    }

    public function setAttributeValue($productId, $attributeCode, $value, $storeId)
    {
        $storeValue = $this->getAttributeValue($productId, $attributeCode, $storeId, true);
        $storeId = $storeValue !== null ? $storeId : self::DEFAULT_STORE_ID;

        $attributeData = $this->getAttributeDataByCode($attributeCode);
        $table = 'catalog_product_entity_' . $attributeData['backend_type'];

        $this->resource->getConnection()->update(
            $this->resource->getTableName($table),
            ['value' => $value],
            [
                'entity_id = ?' => $productId,
                'attribute_id = ?' => $attributeData['attribute_id'],
                'store_id = ?' => $storeId
            ]
        );
    }

    public function getProductParentId($productId)
    {
        $productIds = $this->configurableModel->getParentIdsByChild($productId);

        if(empty($productIds)){
            return false;
        }

        return $productIds[0];
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

    private function getAttributeDataByCode($attributeCode)
    {
        if(!isset($this->attributes[$attributeCode])){

            $select = $this->resource->getConnection()
                ->select()
                ->from(
                    ['eav' => $this->resource->getTableName('eav_attribute')],
                    ['eav.attribute_id', 'eav.backend_type']
                )
                ->where('eav.entity_type_id = ?', \Magento\Catalog\Setup\CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID)
                ->where('eav.attribute_code = ?', $attributeCode);

            $this->attributes[$attributeCode] = $this->resource->getConnection()->fetchRow($select);
        }

        return $this->attributes[$attributeCode];
    }


}