<?php
/**
 * Copyright © 2016  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Block\Sidebar;

/**
 * Blog sidebar rss
 */
class Rss extends \Magento\Framework\View\Element\Template
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'rss_feed';

    /**
     * Available months
     * @var array
     */
    protected $_months;

	/**
     * Retrieve blog identities
     * @return array
     */
	public function getIdentities()
    {
        return [\Magento\Cms\Model\Block::CACHE_TAG . '_blog_rss_widget'  ];
    }

}
