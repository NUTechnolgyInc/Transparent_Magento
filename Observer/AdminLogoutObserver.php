<?php
/**
 * Copyright © TransparentCDN, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TransparentCDN\TransparentEdge\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\Config\ConfigInterface as SessionConfigInterface;
use Psr\Log\LoggerInterface;

class AdminLogoutObserver implements ObserverInterface
{
    private const CDN_COOKIE_NAME = "adm-tcdn";

    /**
     * @var SessionConfigInterface
     */
    protected SessionConfigInterface $sessionConfig;

    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadataFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Observer Construct
     *
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory  $cookieMetadataFactory,
        LoggerInterface        $logger
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->logger = $logger;
    }

    /**
     * Remove the Transparent CDN Cookies
     *
     * @param Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $this->cookieManager->deleteCookie(
            self::CDN_COOKIE_NAME,
            $this->cookieMetadataFactory->createCookieMetadata(
                [
                    "path" => "/",
                    "secure" => false,
                    "http_only" => false
                ]
            )
        );
        return $this;
    }
}