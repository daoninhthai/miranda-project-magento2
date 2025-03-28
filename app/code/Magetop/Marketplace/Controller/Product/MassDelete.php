<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Product;

use Magento\Framework\Controller\ResultFactory;
use Magetop\Marketplace\Controller\Product\Builder;
use Magetop\Marketplace\Controller\Product\Action\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magetop\Marketplace\Model\ProductsFactory;

class MassDelete extends \Magetop\Marketplace\Controller\Product\Product
{
	const PRODUCT_FIELD_ID='entity_id';
	const PRODUCT_MARKET_FIELD_ID='product_id';


    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
	
    protected $productsFactory;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,        
		ProductsFactory $productsFactory,		
        CollectionFactory $collectionFactory
    ) {        
        $this->collectionFactory = $collectionFactory;
        $this->productsFactory = $productsFactory;		
        parent::__construct($context, $productBuilder);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {		
		$registry=$this->_objectManager->get('Magento\Framework\Registry');				
		$registry->register('isSecureArea', true);
        $collection = $this->filterCollection($this->collectionFactory->create());		
        $productDeleted = 0;				
        foreach ($collection->getItems() as $product) {				
            $product->delete();			
            $productDeleted++;
        }
		self::deleteProductMarketCollection();
		$registry->unregister('isSecureArea');
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been deleted.', $productDeleted)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('marketplace/seller/myProducts');
    }
	
	protected function filterCollection(\Magento\Framework\Data\Collection\AbstractDb $collection){
		$ids=$this->getRequest()->getParam('product_selected');
		$collection->addFieldToFilter(self::PRODUCT_FIELD_ID, ['in' => $ids]);
		return $collection;
	}
	
	protected function deleteProductMarketCollection(){
		$ids=$this->getRequest()->getParam('product_selected');		
		foreach($ids as $key=>$id){
			$this->productsFactory->create()
			->load($id,'product_id')
			->delete();			
		}
	}	
}
