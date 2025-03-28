<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Ui\Component\Listing\AssociatedProduct;

use Magento\Catalog\Ui\Component\FilterFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\ObserverInterface;
use Magento\Framework\View\Element\UiComponentInterface;

class Filters implements ObserverInterface
{
    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @param FilterFactory $filterFactory
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        FilterFactory $filterFactory,
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->filterFactory = $filterFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function update(UiComponentInterface $component)
    {
        if (!$component instanceof \Magento\Ui\Component\Filters) {
            return;
        }

        $attributeIds = $component->getContext()->getRequestParam('attribute_ids');
        if ($attributeIds) {
            foreach ($this->getAttributes($attributeIds) as $attribute) {
                $filter = $this->filterFactory->create($attribute, $component->getContext());
                $filter->prepare();
                $component->addComponent($attribute->getAttributeCode(), $filter);
            }
        }
    }

    /**
     * @param array $attributeIds
     * @return mixed
     */
    protected function getAttributes($attributeIds)
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        return $attributeCollection->addFieldToFilter('attribute_code', ['in' => $attributeIds]);
    }
}
