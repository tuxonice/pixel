# Contributing to Pixel

Thanks for your interest in contributing! Here's how to get started.

## Getting Started

1. Fork the repository and clone your fork
2. Copy the environment file: `cp .env.example .env`
3. Build and start the stack: `make build`
4. Run the tests to make sure everything works: `make composer test`

## Development Workflow

1. Create a feature branch from `main`:
   ```bash
   git checkout -b feature/my-change
   ```
2. Make your changes
3. Run the full CI suite locally:
   ```bash
   make phpcs
   make phpstan
   make composer test
   ```
4. Commit with a clear, descriptive message
5. Push and open a pull request

## Code Standards

- **PSR-12** — enforced via PHP CodeSniffer (`make phpcs`)
- **PHPStan level 8** — enforced via PHPStan (`make phpstan`)
- All new code must include unit tests
- Keep pull requests focused — one concern per PR

## Running Tests

```bash
make composer test
```

## Reporting Bugs

Please use the [Bug Report](../../issues/new?template=bug_report.md) issue template.

## Suggesting Features

Please use the [Feature Request](../../issues/new?template=feature_request.md) issue template.

## Security Vulnerabilities

If you discover a security vulnerability, please follow the process described in [SECURITY.md](SECURITY.md). **Do not open a public issue.**

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
