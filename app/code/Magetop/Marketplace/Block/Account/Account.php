<?php
namespace Magetop\Marketplace\Block\Account;

class Account extends \Magento\Framework\View\Element\Template{

	protected $_customerSession;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_directoriesFactory;	
    protected $_sellersCollectionFactory;	
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magetop\Marketplace\Model\ResourceModel\Sellers\CollectionFactory $sellersCollectionFactory,		
		\Magento\Directory\Model\ResourceModel\Country\CollectionFactory $directoriesFactory,
		array $data = []
	){
		$this->_customerSession = $customerSession;	
		$this->_directoriesFactory = $directoriesFactory;
		$this->_sellersCollectionFactory = $sellersCollectionFactory;
		parent::__construct($context, $data);
	}
	
    /**
     * @return array
     */
    public function _getOptionsCountry()
    {
        $options = $this->_directoriesFactory->create()->load()->toOptionArray(false);
        array_unshift($options, ['value' => '', 'label' => __('All Countries')]);
        return $options;
    }

	public function getSellerInfomation(){
		$_dataCustomer=$this->_customerSession->getCustomerData();
		$_customerId=$_dataCustomer->getId();		
		$_data=self::getSellerCollection($_customerId);
		return $_data[0];
	}
	
	protected function getSellerCollection($_customerId){
		$collection=$this->_sellersCollectionFactory->create()->addFieldToFilter('user_id',$_customerId);				
		return $collection->getData();
	}

	public function getUrlImage($url){
		$srcImage = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $url;
		return $srcImage;
	}
}