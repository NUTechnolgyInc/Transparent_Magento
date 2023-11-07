<?php
/**
 * Copyright © TransparentCDN, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TransparentCDN\TransparentEdge\Model;

class PurgeCache
{
    /**
     * @var Api
     */
    private $api;
    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param Api $api
     * @param Config $config
     */
    public function __construct(Api $api, Config $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    /**
     * Send API purge request to invalidate cache by urls
     *
     * @param array $urls
     * @return array|bool|\Magento\Framework\Controller\Result\Json
     */
    public function sendPurgeRequest(array $urls = [])
    {
        $result = $this->api->execute($urls);
        return $result;
    }
}
