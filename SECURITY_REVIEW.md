# Security Review — Action Items

Tracked from the security review performed on 2026-07-04. Fix one by one, check off when done.

---

## [x] 1. Verbose error disclosure (Medium)

**File:** `src/Http/Kernel.php` (catch-all `\Throwable` handler)

`$e->getMessage()` is returned directly to the client in `500` responses, leaking internal
details (file paths, logic, etc.) in production.

**Fix:**
- Add an `APP_DEBUG` env flag.
- When `APP_DEBUG` is falsy, return a generic message (e.g. `"An unexpected error occurred."`)
  instead of `$e->getMessage()`.
- Log the real exception server-side (e.g. `error_log()` or a proper logger) regardless of the
  flag.

---

## [ ] 2. No defense-in-depth category validation in repository (Low)

**File:** `src/Service/ImageRepository.php`

Category path safety currently relies solely on the route regex (`[a-zA-Z0-9_-]+`) defined in
`config/routes.php`. The repository itself does not validate `$category` before building
filesystem paths in `categoryPath()`.

**Fix:**
- Add a guard in `assertCategoryExists()` or `categoryPath()` that rejects any category
  containing `/`, `\`, or `..`, independent of the route-level regex.

---

## [ ] 3. Unbounded `getAllImages()` — DoS via large categories (Low)

**File:** `src/Service/ImageRepository.php` (`getAllImages()`), used by legacy `GET /json/{category}`

No pagination or cap — a category with a very large number of files returns everything in one
response, calling `filesize()` per file.

**Fix:**
- Add a hard cap (e.g. max 1000 files) and document the limit, or reintroduce lightweight
  pagination for this legacy endpoint.

---

## [ ] 4. Rate limiter trusts unproxied client IP (Low)

**File:** `src/Http/Kernel.php` (`$request->getClientIp()`), `docker/nginx/default.conf`

The app runs behind Nginx but Symfony's trusted proxies are not configured, so
`getClientIp()` may return the Nginx container IP for all requests (rate limit bucket shared
by everyone) or become spoofable via `X-Forwarded-For` if proxies are later misconfigured.

**Fix:**
- Call `Request::setTrustedProxies()` with the Nginx hop's IP/range and trust the
  `X-Forwarded-For` header explicitly.
- Verify real client IPs are used with a manual test (curl from two different source IPs, or
  simulate via headers).

---

## [ ] 5. No expiry/cleanup of rate-limit state files (Low)

**File:** `src/Service/RateLimiter.php`, `var/rate_limit/`

One file per unique IP hash is created and never removed, even after the request window has
expired. Long-running deployments accumulate stale files indefinitely (disk exhaustion risk
under sustained scanning/botting).

**Fix:**
- Add a periodic cleanup (cron job, or opportunistic cleanup inside `check()` with low
  probability) that deletes files whose mtime is older than the configured window.

---

## [ ] 6. No `.dockerignore` — bloated/leaky image build (Low)

**File:** `docker/php/Dockerfile` (`COPY . .`)

Without a `.dockerignore`, the build context (and final image layer) includes `.git/`, `tests/`,
`var/`, and any local `.env` present at build time.

**Fix:**
- Add a `.dockerignore` at the project root excluding at least: `.git`, `.env`, `.env.local`,
  `var/`, `tests/`, `node_modules/`, `*.md` (optional).
