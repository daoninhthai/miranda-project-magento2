<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Block\Product\Widget;

/**
 * Button widget
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Button extends \Magento\Framework\View\Element\Template
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Magetop_Marketplace::product/widget/button.phtml');
        parent::_construct();
    }

    /**
     * Retrieve button type
     *
     * @return string
     */
    public function getType()
    {
        if (in_array($this->getData('type'), ['reset', 'submit'])) {
            return $this->getData('type');
        }
        return 'button';
    }

    /**
     * Retrieve onclick handler
     *
     * @return null|string
     */
    public function getOnClick()
    {
        return $this->getData('on_click') ?: $this->getData('onclick');
    }

    /**
     * Retrieve attributes html
     *
     * @return string
     */
    public function getButtonAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (!$title) {
            $title = $this->getLabel();
        }
        $classes = [];
        $classes[] = 'action-default';
        $classes[] = 'scalable';
        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        return $this->_attributesButtonToHtml($this->_prepareButtonAttributes($title, $classes, $disabled));
    }

    /**
     * Prepare attributes
     *
     * @param string $title
     * @param array $classes
     * @param string $disabled
     * @return array
     */
    protected function _prepareButtonAttributes($title, $classes, $disabled)
    {
        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getElementName(),
            'title' => $title,
            'type' => $this->getType(),
            'class' => join(' ', $classes),
            'onclick' => $this->getOnClick(),
            'style' => $this->getStyle(),
            'value' => $this->getValue(),
            'disabled' => $disabled,
        ];
        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }
        return $attributes;
    }

    /**
     * Attributes list to html
     *
     * @param array $attributes
     * @return string
     */
    protected function _attributesButtonToHtml($attributes)
    {
        $html = '';
        foreach ($attributes as $attributeKey => $attributeValue) {
            if ($attributeValue === null || $attributeValue == '') {
                continue;
            }
            $html .= $attributeKey . '="' . $this->escapeHtml($attributeValue) . '" ';
        }

        return $html;
    }
}
