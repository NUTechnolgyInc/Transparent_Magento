<?php
/**
 * Copyright © TransparentCDN, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TransparentCDN\TransparentEdge\Observer;

use TransparentCDN\TransparentEdge\Model\Config;
use TransparentCDN\TransparentEdge\Model\PurgeCache;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManager;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\Block;

class FlushAllCacheObserver implements ObserverInterface
{

    /**
     * App Emulator
     *
     * @var Emulation
     */
    protected $appEmulation;
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var StoreManager
     */
    private $storeManager;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var PurgeCache
     */
    private $purgeCache;

    /**
     * @param StoreManager $storeManager
     * @param Config $config
     * @param PurgeCache $purgeCache
     * @param Emulation $appEmulation
     */
    public function __construct(
        StoreManager $storeManager,
        Config       $config,
        PurgeCache   $purgeCache,
        Emulation    $appEmulation
    ) {
        $this->objectManager = ObjectManager::getInstance();
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->purgeCache = $purgeCache;
        $this->appEmulation = $appEmulation;
    }

    /**
     * Execute Method
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $purgeCache = false;
        $urls = [];
        $event = $observer->getEvent();

        if ($event->getName() == "model_save_after") {
            if ($event->getObject() instanceof Page) {
                $page = $observer->getEvent()->getObject();
                $urls = $this->_getFrontEndPageUrl($page->getId());
                $purgeCache = true;
            }
            //if ($event->getObject() instanceof Block) {
                //@TODO - invalidate urls based on block association
            //}
        }

        if ($event->getName() == "catalog_product_save_after") {
            $product = $observer->getEvent()->getProduct();
            $urls = array_merge(
                $this->_getFrontEndProductUrl($product),
                $this->_getFrontEndProductCategoriesUrl($product),
                $this->_getFrontEndProductImagesUrl($product)
            );
            $purgeCache = true;
        }
        if ($event->getName() == "catalog_category_save_after") {
            $category = $observer->getEvent()->getCategory();
            $urls = $this->_getFrontEndCategoryUrl($category);
            $purgeCache = true;
        }

        if ($event->getName() == "adminhtml_cache_flush_all") {
            $purgeCache = true;
        }

        if ($purgeCache) {
            $this->purgeCache->sendPurgeRequest($urls);
        }
    }

    /**
     * Get FrontEnd Page Url
     *
     * @param int $id
     * @return array
     */
    private function _getFrontEndPageUrl($id)
    {
        $this->appEmulation->startEnvironmentEmulation(null, Area::AREA_FRONTEND, true);
        $pageHelper = $this->objectManager->create(\Magento\Cms\Helper\Page::class);
        $pageUrl = $pageHelper->getPageUrl($id);
        $this->appEmulation->stopEnvironmentEmulation();
        return [$pageUrl];
    }

    /**
     * Get FrontEnd Product Url
     *
     * @param mixed $product
     * @return array
     */
    private function _getFrontEndProductUrl($product)
    {
        $this->appEmulation->startEnvironmentEmulation(null, Area::AREA_FRONTEND, true);
        $catalogModelProduct = $this->objectManager->create(Product::class);
        $frontEndProduct = $catalogModelProduct->loadByAttribute('entity_id', $product->getId());
        $frontEndProductUrl = $frontEndProduct->getProductUrl();
        $this->appEmulation->stopEnvironmentEmulation();
        return [$frontEndProductUrl];
    }

    /**
     * Get FrontEnd Product Categories Url
     *
     * @param mixed $product
     * @return array
     */
    private function _getFrontEndProductCategoriesUrl($product)
    {
        $this->appEmulation->startEnvironmentEmulation(null, Area::AREA_FRONTEND, true);

        $categories = $product->getCategoryIds(); /*will return category ids array*/
        $urls = [];
        foreach ($categories as $category) {
            $cat = $this->objectManager->create(Category::class)->load($category);
            $urls[] = $cat->getUrl();
        }

        $this->appEmulation->stopEnvironmentEmulation();
        return $urls;
    }

    /**
     * Get FrontEnd Product Images Url
     *
     * @param mixed $product
     * @return array
     */
    private function _getFrontEndProductImagesUrl($product)
    {
        $this->appEmulation->startEnvironmentEmulation(null, Area::AREA_FRONTEND, true);

        $imageHelperFactory = $this->objectManager->create(\Magento\Catalog\Helper\ImageFactory::class);
        $imageUrl = $imageHelperFactory->create()->init($product, 'product_base_image')->getUrl();
        $thumbnailImageUrl = $imageHelperFactory->create()->init($product, 'product_thumbnail_image')->getUrl();
        $smallImageUrl = $imageHelperFactory->create()->init($product, 'product_small_image')->getUrl();

        $this->appEmulation->stopEnvironmentEmulation();
        return [$imageUrl, $thumbnailImageUrl, $smallImageUrl];
    }

    /**
     * Get FrontEnd Category Url
     *
     * @param mixed $category
     * @return array
     */
    private function _getFrontEndCategoryUrl($category)
    {
        $this->appEmulation->startEnvironmentEmulation(null, Area::AREA_FRONTEND, true);

        $cat = $this->objectManager->create(Category::class)->load($category->getId());
        $urls[] = $cat->getUrl();

        $parentCategories = $category->getParentIds();
        foreach ($parentCategories as $categoryId) {
            $cat = $this->objectManager->create(Category::class)->load($categoryId);
            if ($cat->getUrlPath()) {
                $urls[] = $cat->getUrl();
            }
        }

        $this->appEmulation->stopEnvironmentEmulation();
        return $urls;
    }
}
