# rrq/vexagame

Laravel package for VexaGame API V2 integration — manage products, transactions, and balance seamlessly.

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-777BB4?logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/laravel-%5E10.0%7C%5E11.0-FF2D20?logo=laravel)](https://laravel.com)

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Get Products](#1-get-products)
  - [Get Product Items (Detail)](#2-get-product-items-detail)
  - [Check Balance](#3-check-balance)
  - [Create Transaction](#4-create-transaction)
  - [Get Transactions](#5-get-transactions)
  - [Get Transaction Detail](#6-get-transaction-detail)
  - [Check Nickname](#7-check-nickname)
- [Error Handling](#error-handling)
- [Callback Configuration](#callback-configuration)
- [Testing](#testing)
- [API Reference](#api-reference)

---

## Requirements

- PHP 8.1+
- Laravel 10.0+ or 11.0+
- GuzzleHTTP 7.0+

---

## Installation

```bash
composer require rrq/vexagame
```

Publish the config file:

```bash
php artisan vendor:publish --tag=vexagame-config
```

---

## Configuration

Add to your `.env`:

```env
VEXAGAME_API_KEY=your_api_key_here
VEXAGAME_IS_PRODUCTION=true
VEXAGAME_TIMEOUT=30
VEXAGAME_CALLBACK_URL=https://your-app.com/callback
VEXAGAME_CALLBACK_TOKEN=your_callback_token
```

The `BASE_URL` is selected automatically:
- `VEXAGAME_IS_PRODUCTION=true` → `https://api.vexaagen.com`
- `VEXAGAME_IS_PRODUCTION=false` → `https://dev.vexapay.vexatechno.com/api`
- You can also set `VEXAGAME_BASE_URL` explicitly to override

Published config file `config/vexagame.php`:

```php
return [
    'api_key' => env('VEXAGAME_API_KEY'),
    'is_production' => env('VEXAGAME_IS_PRODUCTION', true),
    'base_url' => env(
        'VEXAGAME_BASE_URL',
        env('VEXAGAME_IS_PRODUCTION', true)
            ? 'https://api.vexaagen.com'
            : 'https://dev.vexapay.vexatechno.com/api'
    ),
    'timeout' => env('VEXAGAME_TIMEOUT', 30),
    'callback_url' => env('VEXAGAME_CALLBACK_URL'),
    'callback_token' => env('VEXAGAME_CALLBACK_TOKEN'),
];
```

---

## Usage

### Facade

```php
use Rrq\Vexagame\Facades\VexaGameFacade as VexaGame;
```

### Dependency Injection

```php
use Rrq\Vexagame\VexaGame;

class TopUpController extends Controller
{
    public function __construct(protected VexaGame $vexaGame) {}
}
```

---

### 1. Get Products

List all available products, optionally filtered by category.

**Request:**

```php
// All products
$products = VexaGame::getProducts();

// Filtered by category
$products = VexaGame::getProducts(categorySlug: 'games');
```

**Response:**

```json
{
    "code": 200,
    "message": "data successfully retrieved",
    "payload": [
        {
            "id": 1,
            "name": "Free Fire",
            "slug": "free-fire",
            "category": "Games",
            "picture_url": "https://cdn.example.com/free-fire.png"
        },
        {
            "id": 2,
            "name": "Mobile Legends",
            "slug": "mobile-legends",
            "category": "Games",
            "picture_url": "https://cdn.example.com/mobile-legends.png"
        }
    ]
}
```

---

### 2. Get Product Items (Detail)

Get all items/denominations for a specific product.

**Request:**

```php
$items = VexaGame::getProductItems('free-fire');
```

**Response:**

```json
{
    "code": 200,
    "message": "data successfully retrieved",
    "payload": [
        {
            "id": 669,
            "name": "5 Diamond",
            "product_name": "Free Fire",
            "full_name": "Free Fire 5 Diamond",
            "code": "FF5",
            "price": "Rp 1.000",
            "price_raw": 1000,
            "status": "active",
            "stock": 100,
            "description": "Free Fire 5 Diamond",
            "min": 1,
            "max": 10
        },
        {
            "id": 670,
            "name": "12 Diamond",
            "product_name": "Free Fire",
            "full_name": "Free Fire 12 Diamond",
            "code": "FF12",
            "price": "Rp 2.000",
            "price_raw": 2000,
            "status": "active",
            "stock": 50,
            "description": "Free Fire 12 Diamond",
            "min": 1,
            "max": 10
        }
    ]
}
```

---

### 3. Check Balance

Get current account balance.

**Request:**

```php
// Get full profile including balance
$profile = VexaGame::getProfile();

// Get balance only
$balance = VexaGame::getBalance();
```

**Response (`getProfile`):**

```json
{
    "code": 200,
    "message": "data successfully retrieved",
    "payload": {
        "id": 123,
        "name": "John Doe",
        "business_name": "My Store",
        "email": "john@example.com",
        "phone_number": "08123456789",
        "latest_balance": 500000,
        "user_api": {
            "token": "your_api_key",
            "callback_url": "https://your-app.com/callback",
            "callback_token": "callback_token_value"
        }
    }
}
```

**Response (`getBalance`):**

```
500000
```

---

### 4. Create Transaction

Create a new top-up/purchase transaction.

**Request:**

```php
// Basic transaction
$transaction = VexaGame::createTransaction(
    code: 'FF5',
    customerNo: '132132144'
);

// With optional parameters
$transaction = VexaGame::createTransaction(
    code: 'FF5',
    customerNo: '132132144',
    paymentMethod: 'balance',
    qty: 1,
    partnerRefId: 'ORDER-20240101-001',
    maxPrice: 15000
);
```

**Response (Success):**

```json
{
    "code": 200,
    "message": "Transaction created successfully",
    "payload": {
        "id": 78901,
        "code": "TRX-20240101-001",
        "product_code": "FF5",
        "product_name": "Free Fire 5 Diamond",
        "customer_no": "132132144",
        "price": 1000,
        "status": "Dalam Proses",
        "created_at": "2024-01-01 10:00:00"
    },
    "balance": 499000
}
```

**`customer_no` Format:**

| Game | Format | Example |
|------|--------|---------|
| Mobile Legends | `{playerId}{serverId}` | `132132144` |
| Genshin Impact | `{uid}\|{server}` | `123123\|Asia` |
| Free Fire | `{playerId}` | `13287136821` |

---

### 5. Get Transactions

List your transactions with optional filters.

**Request:**

```php
// All transactions
$transactions = VexaGame::getTransactions();

// Filter by product code
$transactions = VexaGame::getTransactions(code: 'FF5');

// With pagination
$transactions = VexaGame::getTransactions(page: 2);
```

**Response:**

```json
{
    "code": 200,
    "message": "data successfully retrieved",
    "payload": {
        "data": [
            {
                "id": 78901,
                "code": "TRX-20240101-001",
                "product_code": "FF5",
                "product_name": "Free Fire 5 Diamond",
                "customer_no": "132132144",
                "price": 1000,
                "status": "Sukses",
                "sn": "ABC123XYZ",
                "created_at": "2024-01-01 10:00:00"
            },
            {
                "id": 78900,
                "code": "TRX-20240101-002",
                "product_code": "FF12",
                "product_name": "Free Fire 12 Diamond",
                "customer_no": "132132144",
                "price": 2000,
                "status": "Dalam Proses",
                "sn": null,
                "created_at": "2024-01-01 09:30:00"
            }
        ],
        "meta": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 15,
            "total": 72
        }
    }
}
```

---

### 6. Get Transaction Detail

Get details of a specific transaction.

**Request:**

```php
$transaction = VexaGame::getTransaction(78901);
```

**Response:**

```json
{
    "code": 200,
    "message": "data successfully retrieved",
    "payload": {
        "id": 78901,
        "code": "TRX-20240101-001",
        "product_code": "FF5",
        "product_name": "Free Fire 5 Diamond",
        "customer_no": "132132144",
        "price": 1000,
        "status": "Sukses",
        "sn": "ABC123XYZ",
        "description": "Pembelian Free Fire berhasil",
        "partner_ref_id": "ORDER-20240101-001",
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-01 10:01:15"
    }
}
```

---

### 7. Check Nickname

Verify a player's game ID before creating a transaction.

**Request:**

```php
$nickname = VexaGame::checkNickname(
    customerNo: '132132144',
    game: 'Free Fire'
);
```

**Response (Free Fire):**

```json
{
    "code": 200,
    "message": "data successfully retrieved",
    "payload": {
        "customer_no": "13287136821",
        "name": "PlayerName",
        "message": "Nickname found"
    }
}
```

**Response (Mobile Legends):**

```json
{
    "code": 200,
    "message": "data successfully retrieved",
    "payload": {
        "customer_no": "132132144",
        "split_customer_no": "13213-2144",
        "name": "PlayerName",
        "message": "Nickname found"
    }
}
```

---

## Error Handling

All methods throw `Rrq\Vexagame\Exceptions\VexaGameException` on failure.

```php
use Rrq\Vexagame\Exceptions\VexaGameException;

try {
    $transaction = VexaGame::createTransaction('FF5', '132132144');
} catch (VexaGameException $e) {
    // HTTP status code
    $e->getStatusCode();        // 400

    // Full error response
    $e->getResponse();          // [...]
    $e->getRequest();           // [...]
    $e->getMessage();           // "Saldo tidak mencukupi"

    // Helper checks
    $e->isInsufficientBalance(); // true
    $e->isProductOutOfStock();   // true/false
    $e->isInvalidCredentials();  // true/false
    $e->isProductNotFound();     // true/false
}
```

### Common Errors

| HTTP | Message | Cause |
|------|---------|-------|
| 400 | `Invalid credentials!` | Missing or invalid API key |
| 400 | `Saldo tidak mencukupi` | Insufficient balance |
| 400 | `Maaf stok produk sudah habis` | Product out of stock |
| 404 | `Product not found` | Invalid product code |
| 500 | `Internal Server Error` | Server-side error, retry |

---

## Callback Configuration

Contact VexaGame support to configure your callback URL. They will send transaction updates via HTTP POST:

```json
{
    "name": "John Doe",
    "code": "TRX123456",
    "status": "Sukses",
    "customer_no": "132132144",
    "service": "Mobile Legends 28 Diamond",
    "description": "Pembelian Diamond Mobile Legends berhasil",
    "sn": "ABC123XYZ",
    "price": 50000
}
```

**Transaction Statuses:**

| Status | Meaning |
|--------|---------|
| `Dalam Proses` | Order in process |
| `Sukses` | Order completed successfully |
| `Gagal` | Order failed |
| `Refund` | Order refunded |

---

## API Reference

| Method | Endpoint | Description |
|--------|----------|-------------|
| `getProducts()` | `GET /v2/product` | List products |
| `getProductItems()` | `GET /v2/product-item` | Product details |
| `getProductCategories()` | `GET /public/product-category` | Product categories |
| `createTransaction()` | `POST /v2/transaction` | Create transaction |
| `getTransactions()` | `GET /v2/transaction` | List transactions |
| `getTransaction()` | `GET /v2/transaction/:id` | Transaction detail |
| `getProfile()` | `GET /v2/me` | User profile |
| `getBalance()` | `GET /v2/me` | Account balance |
| `checkNickname()` | `GET /check-nickname` | Verify game ID |

**Base URLs:**
- Production: `https://api.vexaagen.com`
- Development: `https://dev.vexapay.vexatechno.com/api`

---

## Testing

Tests run inside Docker (PHP 8.1). No local PHP required.

```bash
# Build image (one-time)
docker compose build

# Run tests
docker compose run --rm php vendor/bin/phpunit
```

31 tests, 77 assertions. Uses MockHandler to mock GuzzleHTTP responses.

---

## License

MIT
