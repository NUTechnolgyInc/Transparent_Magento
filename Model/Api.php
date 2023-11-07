<?php
/**
 * Copyright © TransparentCDN, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TransparentCDN\TransparentEdge\Model;

use Psr\Log\LoggerInterface;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\Message\ManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;

class Api
{

    /**
     * API request URL
     */
    public const API_REQUEST_URI = 'https://api.transparentcdn.com';

    /**
     * API version
     */
    public const API_VERSION = 'v1';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Config $config
     * @param InvalidateLogger $logger
     * @param LoggerInterface $log
     * @param ManagerInterface $messageManager
     * @param clientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config                $config,
        InvalidateLogger      $logger,
        LoggerInterface       $log,
        ManagerInterface      $messageManager,
        ClientFactory         $clientFactory,
        ResponseFactory       $responseFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->log = $log;
        $this->messageManager = $messageManager;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute Method
     *
     * @param array $urls
     */
    public function execute(array $urls = []): void
    {
        if ($this->config->getIsEnabled()) {
            $this->purgeAll($urls);
        }
    }

    /**
     * PurgeAll
     *
     * @param array $urls
     */
    public function purgeAll(array $urls = []): void
    {
        $purgeAll = false;
        if (!$urls) {
            $purgeAll = true;
            $urls = [$this->storeManager->getStore()->getBaseUrl()];
        }
        $token = $this->getToken();
        if ($token) {
            $uri = 'companies/' . $this->config->getConfigValue(Config::COMPANY_ID) . '/invalidate/';
            $reqRarams = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode(['urls' => $urls])
            ];
            $response = $this->doRequest($uri, $reqRarams, Request::HTTP_METHOD_POST);

            if ($response->getStatusCode() == 200) {
                $status = $response->getStatusCode(); // 200 status code
                $responseBody = $response->getBody();
                $responseContent = json_decode($responseBody->getContents(), true);
                // here you will have the API response in JSON format
                if ($purgeAll) {
                    $this->messageManager->addSuccessMessage(__('TransparentEdge CDN cache purged.'));
                }
                $this->log->debug('TransparentEdge CDN cache purged. ' . $responseBody->getContents(), $urls);
            } else {
                $this->messageManager->addErrorMessage(__(
                    'TransparentEdge CDN cache could not be purged. Please check your settings and try it again.'
                ));
                $this->log->error('TransparentEdge CDN cache could not be purged.', $urls);
            }
        }
    }

    /**
     * Get Token
     */
    private function getToken(): string
    {
        $uri = 'oauth2/access_token/';
        $uriParams = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->config->getConfigValue(Config::CLIENT_KEY),
            'client_secret' => $this->config->getConfigValue(Config::SECRET_KEY)
        ];
        $reqRarams = [];
        $response = $this->doRequest(
            $uri . '?' . http_build_query($uriParams),
            $reqRarams,
            Request::HTTP_METHOD_POST
        );
        if ($response->getStatusCode() == 200) {
            $status = $response->getStatusCode(); // 200 status code
            $responseBody = $response->getBody();
            $responseContent = json_decode($responseBody->getContents(), true);
            // here you will have the API response in JSON format
            return $responseContent['access_token'];
        } else {
            $this->messageManager->addErrorMessage(__(
                'Could not get TransparentEdge CDN token with actual credentials. ' .
                ' Please check your settings and try it again.'
            ));
            $this->log->error('Could not get TransparentEdge CDN token with actual credentials.');
        }
        return '';
    }

    /**
     * Do API request with provided params
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return Response
     */
    private function doRequest(
        string $uriEndpoint,
        array  $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => self::API_REQUEST_URI . '/' . self::API_VERSION . '/'
        ]]);

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
            $this->log->error(
                'TransparentEdge API GuzzleException: [Status:' . $exception->getCode() . ' message:' .
                $exception->getMessage() . ']'
            );
        }

        return $response;
    }
}
