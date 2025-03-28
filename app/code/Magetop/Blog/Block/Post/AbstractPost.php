<?php
/**
 * Copyright © 2016  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Block\Post;

use Magento\Store\Model\ScopeInterface;

/**
 * Abstract post мшуц block
 */
abstract class AbstractPost extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magetop\Blog\Model\Post
     */
    protected $_post;

    /**
     * Page factory
     *
     * @var \Magetop\Blog\Model\PostFactory
     */
    protected $_postFactory;

    /**
     * @var Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var string
     */
    protected $_defaultPostInfoBlock = 'Magetop\Blog\Block\Post\Info';

    /**
     * @var \Magetop\Blog\Model\Url
     */
    protected $_url;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Cms\Model\Page $post
     * @param \Magento\Framework\Registry $coreRegistry,
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Cms\Model\PageFactory $postFactory
     * @param \Magetop\Blog\Model\Url $url
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetop\Blog\Model\Post $post,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magetop\Blog\Model\PostFactory $postFactory,
        \Magetop\Blog\Model\Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_post = $post;
        $this->_coreRegistry = $coreRegistry;
        $this->_filterProvider = $filterProvider;
        $this->_postFactory = $postFactory;
        $this->_url = $url;
    }

    /**
     * Retrieve post instance
     *
     * @return \Magetop\Blog\Model\Post
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData('post',
                $this->_coreRegistry->registry('current_blog_post')
            );
        }
        return $this->getData('post');
    }

    /**
     * Retrieve post short content
     *
     * @return string
     */
    public function getShorContent()
    {
        $content = $this->getPost()->getContent();
        $pageBraker = '<!-- pagebreak -->';
        
        $isMb = function_exists('mb_strpos');
        $p = $isMb ? strpos($content, $pageBraker) : mb_strpos($content, $pageBraker);

        if ($p) {
            $content = substr($content, 0, $p);
        }

        return $this->_filterProvider->getPageFilter()->filter($content);
    }

    /**
     * Retrieve post content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_filterProvider->getPageFilter()->filter(
            $this->getPost()->getContent()
        );

        return $this->getData($k);
    }

    /**
     * Retrieve post info html
     *
     * @return string
     */
    public function getInfoHtml()
    {
        return $this->getInfoBlock()->toHtml();
    }

    /**
     * Retrieve post info block
     *
     * @return \Magetop\Blog\Block\Post\Info
     */
    public function getInfoBlock()
    {
        $k = 'info_block';
        if (!$this->hasData($k)) {
            $blockName = $this->getPostInfoBlockName();
            if ($blockName) {
                $block = $this->getLayout()->getBlock($blockName);
            }

            if (empty($block)) {
                $block = $this->getLayout()->createBlock($this->_defaultPostInfoBlock, uniqid(microtime()));
            }

            $this->setData($k, $block);
        }

        return $this->getData($k)->setPost($this->getPost());
    }

}
