<?php
/**
 * Magehq
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magehq.com license that is
 * available through the world-wide-web at this URL:
 * https://magehq.com/license.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Magehq
 * @package    Magehq_BuyNow
 * @copyright  Magehq\Copyright (c) 2022 Magehq (https://magehq.com/)
 * @license    https://magehq.com/license.html
 */

namespace Magehq\BuyNow\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Backend system config datetime field renderer
 *
 * @api
 * @since 100.0.2
 */
class AdditionalInfo extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Returns element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html= '<div class="userguide-container" style="border:dotted 1px #ccc; padding:20px;">
        <div id="messages"><ul class="messages"><li class="message message-notice notice" style="list-style: none;"><ul>
        <li style="list-style: none;">&#60;&#63;php echo &#36;this->getLayout()->createBlock("Magehq\BuyNow\Block\Product\ListProduct")->setProduct(&#36;product)
        ->setTemplate("Magehq_BuyNow::buynow-list.phtml")->toHtml(); &#63;&#62;</li></ul></li></ul>
        </div><code>Use this code to add BuyNow button on any product listing.<br>
        NOTE: You need to pass product object (@var $product \Magento\Catalog\Model\Product)</code></div>';

        return $html;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td>'.$this->_getElementHtml($element).'</td>';
        return $this->_decorateRowHtml($element, $html);
    }
}
