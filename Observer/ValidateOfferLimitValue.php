<?php

namespace MageSuite\DailyDeal\Observer;

class ValidateOfferLimitValue implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $productResourceAction;

    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $scope;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Action $productResourceAction,
        \Magento\Framework\Config\ScopeInterface $scope,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->productResourceAction = $productResourceAction;
        $this->scope = $scope;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if($product->getTypeId() != 'simple'){
            return $this;
        }

        if(!$product->getDailyDealLimit()){
            return $this;
        }

        $offerLimit = $product->getDailyDealLimit();

        if(!$product->getExtensionAttributes()){
            return $this;
        }

        $stockItem = $product->getExtensionAttributes()->getStockItem();

        if(!$stockItem){
            return $this;
        }

        $qty = $stockItem->getQty();

        if($qty < $offerLimit){
            $limit = max(0, $qty);

            $this->productResourceAction->updateAttributes(
                [$product->getId()],
                ['daily_deal_limit' => $limit],
                $product->getStoreId()
            );

            if($this->scope->getCurrentScope() == 'adminhtml'){
                $this->messageManager->addNoticeMessage(__('Offer limit cannot be greater than product quantity. This value was fixed automatically to %1', $limit));
            }

        }

        return $this;
    }
}