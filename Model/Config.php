<?php
/**
 * Copyright © TransparentCDN, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TransparentCDN\TransparentEdge\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\PageCache\Model\Config as CoreConfig;
use Magento\Store\Model\ScopeInterface;

class Config extends CoreConfig
{

    public const IS_ENABLED = 'transparentedgecdn/general/cdn_is_enabled';
    public const COMPANY_ID = 'transparentedgecdn/general/cdn_company_id';
    public const CLIENT_KEY = 'transparentedgecdn/general/cdn_client_key';
    public const SECRET_KEY = 'transparentedgecdn/general/cdn_secret_key';
    /** @var ObjectManager $objectManager */
    private $objectManager;
    /** @var ScopeConfigInterface $scopeConfig */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ObjectManager $objectManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ObjectManager        $objectManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is Module Enabled
     *
     * @return ScopeConfigInterface
     */
    public function getIsEnabled()
    {
        return $this->getConfigValue(static::IS_ENABLED);
    }

    /**
     * Get Store Config Data
     *
     * @param string $field
     * @return ScopeConfigInterface
     */
    public function getConfigValue($field)
    {
        $scope = ScopeInterface::SCOPE_STORE;
        $configValue = $this->getScopeConfig()->getValue($field, $scope);
        return $configValue;
    }

    /**
     * Get Scope Config
     *
     * @return ScopeConfigInterface $scopeConfig
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }
}
