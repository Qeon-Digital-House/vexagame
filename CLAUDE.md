# CLAUDE.md — rrq/vexagame

## Project Overview

Laravel package (`rrq/vexagame`) for integrating with the VexaGame API V2. Used for digital product top-ups (game diamonds, vouchers, etc.).

---

## Tech Stack

- **Language:** PHP 8.1+
- **Framework:** Laravel 10 / 11 (as a package)
- **HTTP Client:** GuzzleHTTP 7
- **Package Manager:** Composer
- **Testing:** PHPUnit 10+, Orchestra Testbench, Mockery

---

## Package Architecture

```
rrq/vexagame/
├── composer.json              # Package metadata & autoload config
├── docker-compose.yml         # Docker test environment
├── .docker/
│   └── Dockerfile.dev         # PHP 8.1 CLI with Composer
├── config/
│   └── vexagame.php           # Publishable config (api_key, base_url, timeout)
├── src/
│   ├── VexaGame.php           # Main client class (all API methods)
│   ├── VexaGameServiceProvider.php  # Laravel service provider
│   ├── Facades/
│   │   └── VexaGameFacade.php # Laravel facade
│   └── Exceptions/
│       └── VexaGameException.php   # Custom exception with helper methods
├── tests/
│   ├── MockGuzzleClient.php   # Test trait for mocking GuzzleHTTP
│   ├── VexaGameTest.php       # Unit tests for all API methods
│   └── VexaGameExceptionTest.php  # Unit tests for exception helpers
├── phpunit.xml                # PHPUnit configuration
├── README.md                  # User-facing documentation
└── CLAUDE.md                  # This file
```

---

## System Flow

### Authentication
```
Laravel App
  → config('vexagame.api_key')  →  sent as Authorization header
  → All requests authenticated via API key
```

### Request Flow
```
User Code
  → VexaGame Facade / DI
    → VexaGame::request(method, endpoint, params)
      → GuzzleHTTP Client
        → VexaGame API (api.vexaagen.com)
          → JSON Response or VexaGameException
```

### Core Operations

1. **Product Listing** → `GET /v2/product` — list all products, optional category filter
2. **Product Detail** → `GET /v2/product-item?product_slug=xxx` — get all items/denominations
3. **Create Transaction** → `POST /v2/transaction` — purchase with product code + customer_no
4. **List Transactions** → `GET /v2/transaction` — paginated, optional code filter
5. **Transaction Detail** → `GET /v2/transaction/:id`
6. **Check Balance** → `GET /v2/me` — returns profile with `latest_balance`
7. **Check Nickname** → `GET /check-nickname` — verify game ID before purchase

### Callback Flow (Webhook)
```
VexaGame API  →  POST to configured callback_url
  → { code, status, customer_no, service, sn, price }
  → Statuses: Dalam Proses, Sukses, Gagal, Refund
```

---

## Configuration

| Key | Env Variable | Default |
|-----|-------------|---------|
| API Key | `VEXAGAME_API_KEY` | — |
| Is Production | `VEXAGAME_IS_PRODUCTION` | `true` |
| Base URL | `VEXAGAME_BASE_URL` | auto (based on env) |
| Timeout | `VEXAGAME_TIMEOUT` | `30` |
| Callback URL | `VEXAGAME_CALLBACK_URL` | — |
| Callback Token | `VEXAGAME_CALLBACK_TOKEN` | — |

- `VEXAGAME_IS_PRODUCTION=true` → `https://api.vexaagen.com`
- `VEXAGAME_IS_PRODUCTION=false` → `https://dev.vexapay.vexatechno.com/api`
- `VEXAGAME_BASE_URL` overrides both if set explicitly

---

## Error Handling

All API errors throw `VexaGameException`:
- `isInsufficientBalance()` — "Saldo tidak mencukupi"
- `isProductOutOfStock()` — "stok produk sudah habis"
- `isInvalidCredentials()` — "Invalid credentials"
- `isProductNotFound()` — "Product not found"

---

## customer_no Formats

| Game | Format | Example |
|------|--------|---------|
| Mobile Legends | `{playerId}{serverId}` (concatenated) | `132132144` |
| Genshin Impact | `{uid}\|{server}` (pipe separator) | `123123\|Asia` |
| Free Fire | `{playerId}` (direct) | `13287136821` |

---

## Testing

Tests run inside Docker (PHP 8.1). No local PHP required.

```bash
# Build image (one-time)
docker compose build

# Run tests
docker compose run --rm php vendor/bin/phpunit
```

31 tests, 77 assertions. Uses MockHandler to mock GuzzleHTTP responses.</think><tool_call>
<function=read>
<parameter=filePath>/home/qdh/RRQ/vexagame/CLAUDE.md

---

## External API Docs

Postman Collection: https://documenter.getpostman.com/view/20215986/2sAXxY5ULo
Support: admin@vexagame.com
