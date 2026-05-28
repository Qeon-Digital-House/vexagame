<?php

namespace Rrq\Vexagame;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Rrq\Vexagame\Exceptions\VexaGameException;

class VexaGame
{
    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.vexaagen.com', '/');

        $this->client = new Client([
            'base_uri' => $this->baseUrl . '/',
            'timeout' => $config['timeout'] ?? 30,
            'headers' => [
                'Authorization' => $this->apiKey,
                'Accept' => 'application/json',
            ],
        ]);
    }

    // ==================== PRODUCT METHODS ====================

    /**
     * Get list of products.
     *
     * @param string|null $categorySlug Filter by category slug
     * @return array
     * @throws VexaGameException
     */
    public function getProducts(?string $categorySlug = null): array
    {
        $params = [];

        if ($categorySlug) {
            $params['category_slug'] = $categorySlug;
        }

        return $this->request('GET', 'v2/product', $params);
    }

    /**
     * Get product items/detail by product slug.
     *
     * @param string $productSlug Product slug (e.g., "free-fire")
     * @return array
     * @throws VexaGameException
     */
    public function getProductItems(string $productSlug): array
    {
        return $this->request('GET', 'v2/product-item', [
            'product_slug' => $productSlug,
        ]);
    }

    /**
     * Get product categories.
     *
     * @return array
     * @throws VexaGameException
     */
    public function getProductCategories(): array
    {
        return $this->request('GET', 'public/product-category');
    }

    // ==================== TRANSACTION METHODS ====================

    /**
     * Create a new transaction.
     *
     * @param string $code Product code (e.g., "FF5")
     * @param string $customerNo Customer phone number or game ID
     * @param string $paymentMethod Payment method (default: "balance")
     * @param int $qty Quantity (default: 1)
     * @param string|null $partnerRefId Your reference ID
     * @param int|null $maxPrice Maximum price limit
     * @return array
     * @throws VexaGameException
     */
    public function createTransaction(
        string $code,
        string $customerNo,
        string $paymentMethod = 'balance',
        int $qty = 1,
        ?string $partnerRefId = null,
        ?int $maxPrice = null
    ): array {
        $params = [
            'code' => $code,
            'customer_no' => $customerNo,
            'payment_method' => $paymentMethod,
            'qty' => $qty,
        ];

        if ($partnerRefId !== null) {
            $params['partner_ref_id'] = $partnerRefId;
        }

        if ($maxPrice !== null) {
            $params['max_price'] = $maxPrice;
        }

        return $this->request('POST', 'v2/transaction', $params);
    }

    /**
     * Get list of transactions.
     *
     * @param string|null $code Filter by product code
     * @param int $page Page number
     * @return array
     * @throws VexaGameException
     */
    public function getTransactions(?string $code = null, int $page = 1): array
    {
        $params = ['page' => $page];

        if ($code) {
            $params['code'] = $code;
        }

        return $this->request('GET', 'v2/transaction', $params);
    }

    /**
     * Get transaction detail by ID.
     *
     * @param string|int $id Transaction ID
     * @return array
     * @throws VexaGameException
     */
    public function getTransaction(string|int $id): array
    {
        return $this->request('GET', "v2/transaction/{$id}");
    }

    // ==================== BALANCE / PROFILE METHODS ====================

    /**
     * Get current user profile including balance.
     *
     * @return array
     * @throws VexaGameException
     */
    public function getProfile(): array
    {
        return $this->request('GET', 'v2/me');
    }

    /**
     * Get current balance.
     *
     * @return int
     * @throws VexaGameException
     */
    public function getBalance(): int
    {
        $profile = $this->getProfile();

        return $profile['payload']['latest_balance'] ?? 0;
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Check customer nickname/ID validity.
     *
     * @param string $customerNo Customer game ID
     * @param string $game Game name (e.g., "Free Fire")
     * @return array
     * @throws VexaGameException
     */
    public function checkNickname(string $customerNo, string $game): array
    {
        return $this->request('GET', 'check-nickname', [
            'customer_no' => $customerNo,
            'game' => $game,
        ]);
    }

    /**
     * Make an HTTP request to the VexaGame API.
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @return array
     * @throws VexaGameException
     */
    protected function request(string $method, string $endpoint, array $params = []): array
    {
        try {
            $options = [];

            if (strtoupper($method) === 'GET') {
                $options[RequestOptions::QUERY] = $params;
            } else {
                $options[RequestOptions::JSON] = $params;
            }

            $response = $this->client->request($method, $endpoint, $options);
            $body = $response->getBody()->getContents();

            $decoded = json_decode($body, true);

            return $decoded ?? ['raw' => $body];
        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();
            $responseBody = null;
            $requestParams = $params;

            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                $message = $responseBody['message'] ?? $e->getMessage();
            } else {
                $message = $e->getMessage();
            }

            throw new VexaGameException(
                $message,
                $statusCode,
                $responseBody,
                $requestParams
            );
        }
    }
}
