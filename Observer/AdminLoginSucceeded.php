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
use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Session\Config\ConfigInterface as SessionConfigInterface;
use Psr\Log\LoggerInterface;

class AdminLoginSucceeded implements ObserverInterface
{
    private const CDN_COOKIE_NAME = "adm-tcdn";
    private const CDN_COOKIE_VALUE = "on";

    /**
     * @var SessionConfigInterface
     */
    protected SessionConfigInterface $sessionConfig;

    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

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
     * @param ConfigInterface $config
     * @param SessionConfigInterface $sessionConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory  $cookieMetadataFactory,
        ConfigInterface        $config,
        SessionConfigInterface $sessionConfig,
        LoggerInterface        $logger
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->config = $config;
        $this->sessionConfig = $sessionConfig;
        $this->logger = $logger;
    }

    /**
     * Add the Transparent CDN Cookies
     *
     * @param Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $lifetime = $this->config->getValue(Session::XML_PATH_SESSION_LIFETIME);
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($lifetime)
            ->setPath($this->sessionConfig->getCookiePath());
        $this->cookieManager->setPublicCookie(
            self::CDN_COOKIE_NAME,
            self::CDN_COOKIE_VALUE,
            $cookieMetadata
        );
        return $this;
    }
}
