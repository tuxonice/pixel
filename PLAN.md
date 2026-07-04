# Pixel v2 — PHP Image Server: Implementation Plan

## Overview

A PHP 8.3+ MVC micro-framework (built on Symfony components, no database) that serves images stored in a local folder hierarchy. Categories are derived from folder names. Provides a JSON list endpoint and a random-image redirect endpoint, with API rate limiting.

---

## Folder Structure

```
pixel-v2/
├── images/                      # Image storage root (pre-existing)
│   ├── nature/
│   ├── food/
│   └── travel/
├── public/                      # Web root (document root for the server)
│   └── index.php                # Front controller
├── src/
│   ├── Controller/
│   │   └── ImageController.php  # Handles all image routes
│   ├── Service/
│   │   ├── ImageRepository.php  # Scans folders, lists/filters images
│   │   └── RateLimiter.php      # IP-based rate limiting (file-based)
│   ├── Http/
│   │   ├── Kernel.php           # Boots framework, dispatches request
│   │   └── Router.php           # Thin wrapper around symfony/routing
│   └── Exception/
│       ├── CategoryNotFoundException.php
│       └── RateLimitExceededException.php
├── config/
│   └── routes.php               # Route definitions
├── var/
│   └── rate_limit/              # Per-IP rate limit state files (gitignored)
├── docker/
│   ├── php/
│   │   └── Dockerfile           # PHP 8.3-fpm image
│   └── nginx/
│       └── default.conf         # Nginx vhost config
├── docker-compose.yml           # Services: php-fpm, nginx
├── composer.json
├── .htaccess                    # Rewrite all to public/index.php (Apache)
└── PLAN.md
```

---

## Docker Local Environment

Two services via `docker-compose.yml`:

| Service | Image | Role |
|---|---|---|
| `php` | `php:8.3-fpm` (custom) | Runs PHP-FPM, mounts project root |
| `nginx` | `nginx:alpine` | Serves static files from `images/` and proxies PHP requests to FPM |

- `docker/php/Dockerfile` — installs Composer, required PHP extensions.
- `docker/nginx/default.conf` — document root → `public/`, passes `.php` to FPM, serves `images/` directly as static files.
- `.env` `APP_BASE_URL` set to `http://localhost:8080` for dev.
- Start with: `docker compose up -d`
- App available at `http://localhost:8080`.

---

## Symfony Components Used

| Component | Purpose |
|---|---|
| `symfony/http-foundation` | `Request` / `Response` / `BinaryFileResponse` |
| `symfony/routing` | Route collection, URL matcher, URL generator |
| `symfony/http-kernel` | `HttpKernelInterface`, event-driven dispatch |
| `symfony/dependency-injection` | Service container |
| `symfony/config` | Config loading (optional, for route resources) |
| `symfony/dotenv` | `.env` support for base URL, image root path, rate limit config |

---

## API Endpoints

### `GET /api/{category}/images`

Returns a paginated JSON list of images in a category.

**Query params:**
- `page` (int, default 1)
- `per_page` (int, default 20, max 100)

**Response `200`:**
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
      "url": "https://example.com/images/nature/forest.jpg",
      "size": 204800,
      "mime": "image/jpeg"
    }
  ]
}
```

**Response `404`** — category folder does not exist.  
**Response `429`** — rate limit exceeded.

---

### `GET /api/{category}/random`

Redirects (`302`) to a random image file URL within the category.

**Response `302`** — `Location: https://example.com/images/nature/forest.jpg`  
**Response `404`** — category not found or folder empty.  
**Response `429`** — rate limit exceeded.

---

### `GET /api/categories`

Returns a JSON list of all available categories (folder names).

**Response `200`:**
```json
{
  "categories": ["food", "nature", "travel"]
}
```

---

## MVC Design

### Front Controller (`public/index.php`)
- Creates `Request` from globals.
- Boots the DI container.
- Passes request to `Kernel`.

### Kernel (`src/Http/Kernel.php`)
- Loads routes from `config/routes.php`.
- Runs `RateLimiter` middleware check before dispatch.
- Matches route via `symfony/routing` `UrlMatcher`.
- Resolves the controller from the container.
- Calls the controller action, returns a `Response`.
- Handles `CategoryNotFoundException` → 404 JSON.
- Handles `RateLimitExceededException` → 429 JSON.

### Router (`src/Http/Router.php`)
- Wraps `RouteCollection` + `UrlMatcher` + `UrlGenerator`.
- Routes defined in `config/routes.php` (plain PHP, no YAML).

### ImageController (`src/Controller/ImageController.php`)
- `listImages(Request $request, string $category): JsonResponse`
- `randomImage(Request $request, string $category): RedirectResponse`
- `listCategories(Request $request): JsonResponse`

### ImageRepository (`src/Service/ImageRepository.php`)
- `getCategories(): array` — scans `images/` for directories.
- `getImages(string $category, int $page, int $perPage): array` — scans a category folder, filters by allowed extensions (`jpg`, `jpeg`, `png`, `gif`), paginates, builds absolute URLs.
- `getRandomImage(string $category): ?string` — returns absolute URL of a random image.
- `categoryExists(string $category): bool`

### RateLimiter (`src/Service/RateLimiter.php`)
- IP-based, no database — uses files in `var/rate_limit/`.
- Configurable: `MAX_REQUESTS` per `WINDOW_SECONDS` (via `.env`).
- Throws `RateLimitExceededException` with `Retry-After` header value.

---

## Configuration (`.env`)

```dotenv
APP_BASE_URL=https://example.com
IMAGES_ROOT=../images
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
```

---

## Implementation Steps

1. **Docker setup** — `docker-compose.yml`, `docker/php/Dockerfile`, `docker/nginx/default.conf`.
2. **Scaffold** — `composer.json`, install Symfony components, create directory structure.
3. **Front controller** — `public/index.php`, `.htaccess`.
4. **DI Container** — wire services in `Kernel` or a dedicated `config/services.php`.
5. **Router** — `Router.php` + `config/routes.php` with the three routes.
6. **Kernel** — request lifecycle: rate limit → match → dispatch → respond.
7. **ImageRepository** — folder scanning, pagination, URL building.
8. **RateLimiter** — file-based sliding window.
9. **ImageController** — three actions using the repository.
10. **Error handling** — custom exceptions → JSON error responses.
11. **Testing** — manual with `curl`; optionally add PHPUnit for repository/rate limiter.

---

## Notes & Constraints

- No database; all state is filesystem-based.
- Images are served **statically** by the web server (Apache/Nginx) directly from `images/`; the PHP app only generates the URLs.
- Rate limiting is per-IP using file locks to avoid race conditions.
- Category names are sanitized (alphanumeric + hyphen/underscore only) to prevent path traversal.
- Supported MIME types: `image/jpeg`, `image/png`, `image/gif`.
