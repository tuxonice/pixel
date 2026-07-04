# Pixel

A PHP 8.4 image server built on a custom MVC micro-framework using Symfony components. Serves images stored in a local folder hierarchy — no database required. Categories are derived automatically from folder names.

## Requirements

- Docker
- Docker Compose
- Make

## Getting Started

### 1. Clone and configure

```bash
git clone <repo-url>
cd pixel
cp .env.example .env
```

Edit `.env` to match your setup:

```dotenv
APP_BASE_URL=http://localhost:8080
APP_DEBUG=false
IMAGES_ROOT=/var/www/html/images
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
TRUSTED_PROXIES=
```

`TRUSTED_PROXIES` is a comma-separated list of proxy IPs/CIDR ranges. Leave it empty unless the
app sits behind an additional reverse proxy or load balancer upstream of Nginx — only then should
`X-Forwarded-For` be trusted for rate limiting and client IP resolution.

Set `APP_DEBUG=true` only in local development to expose real exception messages in `500`
responses. Keep it `false` (default) in any shared or production environment.

### 2. Add images

Place images inside the `images/` directory, one subfolder per category:

```
images/
├── nature/
│   ├── forest.jpg
│   └── sunset.png
├── food/
│   └── pizza.jpg
└── travel/
    └── paris.gif
```

Supported formats: `jpg`, `jpeg`, `png`, `gif`.

### 3. Start the stack

```bash
make build
```

The app will be available at **http://localhost:8080**.

## Make Commands

| Command | Description |
|---|---|
| `make build` | Build images and start containers |
| `make up` | Start containers (after first build) |
| `make start` | Start previously stopped containers |
| `make stop` | Stop containers without removing them |
| `make down` | Stop and remove containers |
| `make restart` | Restart all services |
| `make logs` | Follow logs from all services |
| `make logs-php` | Follow PHP-FPM logs |
| `make logs-nginx` | Follow Nginx logs |
| `make ps` | List running containers |
| `make shell` | Open a shell inside the PHP container |
| `make composer <cmd>` | Run a Composer command, e.g. `make composer require foo/bar` |
| `make phpcs` | Check code style (PSR-12) |
| `make phpcs-fix` | Auto-fix code style violations |
| `make phpstan` | Run static analysis (level 8) |
| `make composer test` | Run PHPUnit test suite |

## API Endpoints

An interactive index page is available at `GET /` listing all endpoints.

### v2

#### List categories

```
GET /api/v2/categories
```

```json
{
  "categories": ["food", "nature", "travel"]
}
```

---

#### List images for a category

```
GET /api/v2/{category}/images
```

| Parameter | Type | Default | Max |
|---|---|---|---|
| `page` | int | `1` | — |
| `per_page` | int | `20` | `100` |

```json
{
  "category": "nature",
  "page": 1,
  "per_page": 20,
  "total": 143,
  "total_pages": 8,
  "images": [
    {
      "filename": "forest.jpg",
      "url": "http://localhost:8080/images/nature/forest.jpg",
      "size": 204800,
      "mime": "image/jpeg"
    }
  ]
}
```

---

#### Random image from a category

```
GET /api/v2/{category}/random
```

Streams a random image from the given category (binary response, correct `Content-Type`).

---

### v1 — Legacy

| Endpoint | Description |
|---|---|
| `GET /api/v1/{category}` | Random image from a specific category |
| `GET /api/v1/` | Random image from any category |

---

### JSON — Legacy

| Endpoint | Description |
|---|---|
| `GET /json` | List all categories |
| `GET /json/{category}` | List all images in a category (no pagination) |

---

### Error responses

| Status | Meaning |
|---|---|
| `404` | Category not found or route not matched |
| `405` | Method not allowed |
| `429` | Rate limit exceeded — check the `Retry-After` header |
| `500` | Internal server error |

## Rate Limiting

IP-based sliding window rate limiter — no database. State is stored in `var/rate_limit/`.

Configure via `.env`:

```dotenv
RATE_LIMIT_MAX=60     # max requests allowed
RATE_LIMIT_WINDOW=60  # window size in seconds
```

When the limit is exceeded the API returns `429` with a `Retry-After: <seconds>` header.

## Project Structure

```
pixel/
├── .github/workflows/
│   └── ci.yml              # GitHub Actions CI
├── config/
│   ├── routes.php          # Route definitions
│   └── services.php        # DI container wiring
├── docker/
│   ├── php/Dockerfile      # PHP 8.4-fpm image
│   └── nginx/default.conf  # Nginx vhost
├── images/                 # Image storage root
├── public/
│   └── index.php           # Front controller
├── src/
│   ├── Controller/
│   │   ├── ImageController.php
│   │   └── IndexController.php
│   ├── Exception/
│   │   ├── CategoryNotFoundException.php
│   │   └── RateLimitExceededException.php
│   ├── Http/
│   │   ├── Kernel.php      # Request lifecycle
│   │   └── Router.php      # Routing wrapper
│   └── Service/
│       ├── ImageRepository.php
│       └── RateLimiter.php
├── templates/
│   └── index.html.twig     # API index page
├── tests/
│   ├── Exception/
│   └── Service/
├── var/rate_limit/         # Rate limit state files (gitignored)
├── .env                    # Environment config
├── docker-compose.yml
├── phpunit.xml
└── Makefile
```

## Tech Stack

- **PHP 8.4** with PHP-FPM
- **Nginx** — static file serving + PHP proxy
- **Symfony Components** — `http-foundation`, `routing`, `http-kernel`, `dependency-injection`, `config`, `dotenv`
- **Twig 3** — HTML template rendering
- **PHPUnit 11** — unit tests
- **PHPStan** — static analysis at level 8
- **PHP CodeSniffer** — PSR-12 code style
- **GitHub Actions** — CI on every push and pull request
