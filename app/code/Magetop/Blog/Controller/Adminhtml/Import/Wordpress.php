<?php
/**
 * Copyright © 2016  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Controller\Adminhtml\Import;

/**
 * Blog prepare wordpress import controller
 */
class Wordpress extends \Magento\Backend\App\Action
{
	/**
     * Prepare wordpress import
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magetop_Blog::import');
        $title = __('Blog Import from WordPress (beta)');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);

        $config = new \Magento\Framework\DataObject(
            (array)$this->_getSession()->getData('import_wordpress_form_data', true) ?: array()
        );

        $this->_objectManager->get('\Magento\Framework\Registry')->register('import_config', $config);

        $this->_view->renderLayout();
    }
}
