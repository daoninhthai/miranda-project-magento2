<?php
namespace Magetop\SellerFedExShipping\Model\Source;

class Type extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const USE_API_ADMIN = "admin";
    const USE_API_SELLER = "seller";
    /**
     * Options array
     *
     * @var array
     */
    protected $_options = null;

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options =  [
                ['value' => self::USE_API_ADMIN ,
                    'label' => __('Using api information of admin')],
                ['value' => self::USE_API_SELLER,
                    'label' => __('Using api information of seller')]
            ];
        }
        return $this->_options;
    }

    /**
     * Options getterRussian
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }


    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->toOptionArray() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }


}
