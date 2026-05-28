<?php

namespace Rrq\Vexagame\Tests;

use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\TestCase;
use Rrq\Vexagame\Exceptions\VexaGameException;
use Rrq\Vexagame\VexaGame;

class VexaGameTest extends TestCase
{
    use MockGuzzleClient;

    // ==================== PRODUCT TESTS ====================

    public function testGetProducts(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                ['id' => 1, 'name' => 'Free Fire', 'slug' => 'free-fire'],
                ['id' => 2, 'name' => 'Mobile Legends', 'slug' => 'mobile-legends'],
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getProducts();

        $this->assertEquals(200, $result['code']);
        $this->assertCount(2, $result['payload']);
        $this->assertEquals('Free Fire', $result['payload'][0]['name']);
    }

    public function testGetProductsWithCategoryFilter(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                ['id' => 1, 'name' => 'Free Fire', 'category' => 'Games'],
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getProducts('games');

        $this->assertEquals(200, $result['code']);
        $this->assertCount(1, $result['payload']);
    }

    public function testGetProductItems(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                [
                    'id' => 669,
                    'name' => '5 Diamond',
                    'product_name' => 'Free Fire',
                    'full_name' => 'Free Fire 5 Diamond',
                    'code' => 'FF5',
                    'price' => 'Rp 1.000',
                    'price_raw' => 1000,
                    'status' => 'active',
                    'stock' => 100,
                ],
                [
                    'id' => 670,
                    'name' => '12 Diamond',
                    'product_name' => 'Free Fire',
                    'code' => 'FF12',
                    'price' => 'Rp 2.000',
                    'price_raw' => 2000,
                    'status' => 'active',
                    'stock' => 50,
                ],
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getProductItems('free-fire');

        $this->assertEquals(200, $result['code']);
        $this->assertCount(2, $result['payload']);
        $this->assertEquals('FF5', $result['payload'][0]['code']);
        $this->assertEquals(1000, $result['payload'][0]['price_raw']);
    }

    public function testGetProductCategories(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                ['id' => 1, 'name' => 'Games', 'slug' => 'games'],
                ['id' => 2, 'name' => 'Pulsa', 'slug' => 'pulsa'],
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getProductCategories();

        $this->assertEquals(200, $result['code']);
        $this->assertCount(2, $result['payload']);
        $this->assertEquals('Games', $result['payload'][0]['name']);
    }

    // ==================== TRANSACTION TESTS ====================

    public function testCreateTransaction(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'Transaction created successfully',
            'payload' => [
                'id' => 78901,
                'code' => 'TRX-20240101-001',
                'product_code' => 'FF5',
                'product_name' => 'Free Fire 5 Diamond',
                'customer_no' => '132132144',
                'price' => 1000,
                'status' => 'Dalam Proses',
                'created_at' => '2024-01-01 10:00:00',
            ],
            'balance' => 499000,
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->createTransaction('FF5', '132132144');

        $this->assertEquals(200, $result['code']);
        $this->assertEquals('TRX-20240101-001', $result['payload']['code']);
        $this->assertEquals('FF5', $result['payload']['product_code']);
        $this->assertEquals('132132144', $result['payload']['customer_no']);
        $this->assertEquals(499000, $result['balance']);
    }

    public function testCreateTransactionWithOptionalParams(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'Transaction created successfully',
            'payload' => [
                'id' => 78902,
                'code' => 'TRX-20240101-002',
                'partner_ref_id' => 'ORDER-001',
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->createTransaction(
            code: 'FF12',
            customerNo: '13287136821',
            paymentMethod: 'balance',
            qty: 1,
            partnerRefId: 'ORDER-001',
            maxPrice: 15000
        );

        $this->assertEquals(200, $result['code']);
        $this->assertEquals('ORDER-001', $result['payload']['partner_ref_id']);
    }

    public function testGetTransactions(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                'data' => [
                    [
                        'id' => 78901,
                        'code' => 'TRX-20240101-001',
                        'product_code' => 'FF5',
                        'customer_no' => '132132144',
                        'price' => 1000,
                        'status' => 'Sukses',
                    ],
                    [
                        'id' => 78900,
                        'code' => 'TRX-20240101-002',
                        'product_code' => 'FF12',
                        'customer_no' => '132132144',
                        'price' => 2000,
                        'status' => 'Dalam Proses',
                    ],
                ],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 5,
                    'total' => 72,
                ],
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getTransactions();

        $this->assertEquals(200, $result['code']);
        $this->assertCount(2, $result['payload']['data']);
        $this->assertEquals(72, $result['payload']['meta']['total']);
    }

    public function testGetTransactionsWithCodeFilter(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                'data' => [
                    ['id' => 78901, 'product_code' => 'FF5'],
                ],
                'meta' => ['current_page' => 1, 'total' => 1],
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getTransactions('FF5');

        $this->assertEquals(200, $result['code']);
        $this->assertCount(1, $result['payload']['data']);
        $this->assertEquals('FF5', $result['payload']['data'][0]['product_code']);
    }

    public function testGetTransactionsWithPagination(): void
    {
        $expected = [
            'code' => 200,
            'payload' => [
                'data' => [],
                'meta' => ['current_page' => 3, 'total' => 72],
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getTransactions(page: 3);

        $this->assertEquals(3, $result['payload']['meta']['current_page']);
    }

    public function testGetTransaction(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                'id' => 78901,
                'code' => 'TRX-20240101-001',
                'product_code' => 'FF5',
                'product_name' => 'Free Fire 5 Diamond',
                'customer_no' => '132132144',
                'price' => 1000,
                'status' => 'Sukses',
                'sn' => 'ABC123XYZ',
                'created_at' => '2024-01-01 10:00:00',
                'updated_at' => '2024-01-01 10:01:15',
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getTransaction(78901);

        $this->assertEquals(200, $result['code']);
        $this->assertEquals(78901, $result['payload']['id']);
        $this->assertEquals('Sukses', $result['payload']['status']);
        $this->assertEquals('ABC123XYZ', $result['payload']['sn']);
    }

    public function testGetTransactionWithStringId(): void
    {
        $expected = [
            'code' => 200,
            'payload' => ['id' => 123, 'status' => 'Sukses'],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getTransaction('123');

        $this->assertEquals('Sukses', $result['payload']['status']);
    }

    // ==================== PROFILE / BALANCE TESTS ====================

    public function testGetProfile(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                'id' => 123,
                'name' => 'John Doe',
                'business_name' => 'My Store',
                'email' => 'john@example.com',
                'phone_number' => '08123456789',
                'latest_balance' => 500000,
                'user_api' => [
                    'token' => 'test-api-key',
                    'callback_url' => 'https://your-app.com/callback',
                    'callback_token' => 'callback_token_value',
                ],
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->getProfile();

        $this->assertEquals(200, $result['code']);
        $this->assertEquals('John Doe', $result['payload']['name']);
        $this->assertEquals('My Store', $result['payload']['business_name']);
        $this->assertEquals(500000, $result['payload']['latest_balance']);
    }

    public function testGetBalance(): void
    {
        $expected = [
            'code' => 200,
            'payload' => [
                'id' => 123,
                'name' => 'John Doe',
                'latest_balance' => 750000,
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $balance = $vexaGame->getBalance();

        $this->assertEquals(750000, $balance);
    }

    public function testGetBalanceReturnsZeroWhenMissing(): void
    {
        $expected = [
            'code' => 200,
            'payload' => ['id' => 123, 'name' => 'John Doe'],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $balance = $vexaGame->getBalance();

        $this->assertEquals(0, $balance);
    }

    // ==================== CHECK NICKNAME TESTS ====================

    public function testCheckNickname(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                'customer_no' => '13287136821',
                'name' => 'PlayerName',
                'message' => 'Nickname found',
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->checkNickname('13287136821', 'Free Fire');

        $this->assertEquals(200, $result['code']);
        $this->assertEquals('PlayerName', $result['payload']['name']);
        $this->assertEquals('13287136821', $result['payload']['customer_no']);
    }

    public function testCheckNicknameMobileLegends(): void
    {
        $expected = [
            'code' => 200,
            'message' => 'data successfully retrieved',
            'payload' => [
                'customer_no' => '132132144',
                'split_customer_no' => '13213-2144',
                'name' => 'MLPlayer',
                'message' => 'Nickname found',
            ],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(200, $expected),
        ]));

        $result = $vexaGame->checkNickname('132132144', 'Mobile Legends');

        $this->assertEquals('MLPlayer', $result['payload']['name']);
        $this->assertEquals('13213-2144', $result['payload']['split_customer_no']);
    }

    // ==================== ERROR HANDLING TESTS ====================

    public function testThrowsExceptionOnInvalidCredentials(): void
    {
        $errorBody = [
            'method' => 'POST',
            'code' => 400,
            'message' => 'Invalid credentials!',
            'request' => ['code' => 'FF5'],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(400, $errorBody),
        ]));

        $this->expectException(VexaGameException::class);
        $this->expectExceptionMessage('Invalid credentials!');

        $vexaGame->createTransaction('FF5', '132132144');
    }

    public function testThrowsExceptionOnInsufficientBalance(): void
    {
        $errorBody = [
            'method' => 'POST',
            'code' => 400,
            'message' => 'Saldo tidak mencukupi',
            'request' => ['code' => 'FF5'],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(400, $errorBody),
        ]));

        try {
            $vexaGame->createTransaction('FF5', '132132144');
            $this->fail('Expected VexaGameException');
        } catch (VexaGameException $e) {
            $this->assertTrue($e->isInsufficientBalance());
            $this->assertFalse($e->isProductOutOfStock());
            $this->assertEquals(400, $e->getStatusCode());
            $this->assertNotNull($e->getResponse());
            $this->assertNotNull($e->getRequest());
        }
    }

    public function testThrowsExceptionOnProductOutOfStock(): void
    {
        $errorBody = [
            'method' => 'POST',
            'code' => 400,
            'message' => 'Maaf stok produk sudah habis',
            'request' => ['code' => 'FF17'],
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(400, $errorBody),
        ]));

        try {
            $vexaGame->createTransaction('FF17', '13287136821');
            $this->fail('Expected VexaGameException');
        } catch (VexaGameException $e) {
            $this->assertTrue($e->isProductOutOfStock());
            $this->assertFalse($e->isInsufficientBalance());
        }
    }

    public function testThrowsExceptionOnProductNotFound(): void
    {
        $errorBody = [
            'code' => 404,
            'message' => 'Product not found',
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(404, $errorBody),
        ]));

        try {
            $vexaGame->getTransaction(99999);
            $this->fail('Expected VexaGameException');
        } catch (VexaGameException $e) {
            $this->assertTrue($e->isProductNotFound());
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    public function testExceptionNoHelperMatches(): void
    {
        $errorBody = [
            'code' => 500,
            'message' => 'Internal Server Error',
        ];

        $vexaGame = $this->createVexaGame(new MockHandler([
            $this->mockResponse(500, $errorBody),
        ]));

        try {
            $vexaGame->getProducts();
            $this->fail('Expected VexaGameException');
        } catch (VexaGameException $e) {
            $this->assertFalse($e->isInsufficientBalance());
            $this->assertFalse($e->isProductOutOfStock());
            $this->assertFalse($e->isInvalidCredentials());
            $this->assertFalse($e->isProductNotFound());
        }
    }

    // ==================== CONSTRUCTION TESTS ====================

    public function testConstructUsesDefaultBaseUrl(): void
    {
        $vexaGame = new VexaGame(['api_key' => 'key']);

        $reflection = new \ReflectionClass($vexaGame);
        $property = $reflection->getProperty('baseUrl');
        $this->assertEquals('https://api.vexaagen.com', $property->getValue($vexaGame));
    }

    public function testConstructUsesProvidedBaseUrl(): void
    {
        $vexaGame = new VexaGame([
            'api_key' => 'key',
            'base_url' => 'https://dev.example.com/api/',
        ]);

        $reflection = new \ReflectionClass($vexaGame);
        $property = $reflection->getProperty('baseUrl');
        $this->assertEquals('https://dev.example.com/api', $property->getValue($vexaGame));
    }
}
