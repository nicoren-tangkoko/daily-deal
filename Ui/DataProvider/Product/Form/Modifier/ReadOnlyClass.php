<?php

namespace MageSuite\DailyDeal\Ui\DataProvider\Product\Form\Modifier;

class ReadOnlyClass extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    protected $arrayManager;

    public function __construct(
        \Magento\Framework\Stdlib\ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    public function modifyMeta(array $meta)
    {
        $meta = $this->addReadOnly($meta);

        return $meta;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    protected function addReadOnly($meta)
    {
        $field = 'daily_deal_enabled';

        $elementPath = $this->arrayManager->findPath($field, $meta, null, 'children');

        if (!$elementPath) {
            return $meta;
        }

        $meta = $this->arrayManager->set(
            $elementPath . '/arguments/data/config/disabled',
            $meta,
            true
        );

        return $meta;
    }
}
