<?php

namespace Magetop\Marketplace\Block\Product;


class Bundle extends \Magetop\Marketplace\Block\Product\Widget
{
    /**
     * @var mixed
     */
    protected $_product = null;

    /**
     * @var string
     */
    protected $_template = 'Magetop_Marketplace::product/bundle/bundle.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\Code\NameBuilder $nameBuilder
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Math\Random $mathRandom,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Framework\Code\NameBuilder $nameBuilder,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $mathRandom, $formKey, $nameBuilder, $data);
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', true);
        $this->addChild(
            'add_button',
            'Magetop\Marketplace\Block\Product\Widget\Button',
            [
                'label' => __('Create New Option'),
                'class' => 'add',
                'id' => 'add_new_option',
                'on_click' => 'bOption.add()'
            ]
        );

        $this->setChild(
            'options_box',
            $this->getLayout()->createBlock(
                'Magetop\Marketplace\Block\Product\Bundle\Option',
                'marketplace_product_edit_bundle_option'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Check block readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getCompositeReadonly();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return string
     */
    public function getOptionsBoxHtml()
    {
        return $this->getChildHtml('options_box');
    }

    /**
     * @return string
     */
    public function getFieldSuffix()
    {
        return 'product';
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Bundle Items');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Bundle Items');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get parent tab code
     *
     * @return string
     */
    public function getParentTab()
    {
        return 'product-details';
    }
}
