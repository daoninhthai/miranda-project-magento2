<?php
namespace Magetop\Blog\Controller\Router;

/**
 * Interceptor class for @see \Magetop\Blog\Controller\Router
 */
class Interceptor extends \Magetop\Blog\Controller\Router implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\ActionFactory $actionFactory, \Magento\Framework\Event\ManagerInterface $eventManager, \Magetop\Blog\Model\Url $url, \Magetop\Blog\Model\PostFactory $postFactory, \Magetop\Blog\Model\CategoryFactory $categoryFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\App\ResponseInterface $response)
    {
        $this->___init();
        parent::__construct($actionFactory, $eventManager, $url, $postFactory, $categoryFactory, $storeManager, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'match');
        return $pluginInfo ? $this->___callPlugins('match', func_get_args(), $pluginInfo) : parent::match($request);
    }
}
