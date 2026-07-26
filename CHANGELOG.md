# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- v2 API with paginated image listing and random image streaming
- v1 legacy API for random images by category or from any category
- Legacy JSON API for category and image listing
- IP-based sliding window rate limiter with file-based storage
- Automatic rate-limit state file cleanup
- Trusted proxy support via `TRUSTED_PROXIES` env variable
- `APP_DEBUG` flag to control error verbosity in responses
- Monolog-based daily-rotating log files
- Category path traversal protection
- Docker-based development environment (PHP-FPM + Nginx)
- GitHub Actions CI pipeline (PHPUnit, PHPStan, PHP CodeSniffer, Composer audit)
- Interactive API index page rendered with Twig
