DC = docker compose

.PHONY: help up start down restart build logs logs-php logs-nginx shell composer ps stop phpcs phpcs-fix phpstan

.DEFAULT_GOAL := help

help:
	@echo ""
	@echo "Usage: make [target]"
	@echo ""
	@echo "  Docker"
	@echo "    up            Start containers in background"
	@echo "    start         Start stopped containers"
	@echo "    stop          Stop running containers"
	@echo "    down          Stop and remove containers"
	@echo "    restart       Restart containers"
	@echo "    build         Rebuild and start containers"
	@echo "    ps            List running containers"
	@echo ""
	@echo "  Logs"
	@echo "    logs          Follow all logs"
	@echo "    logs-php      Follow PHP logs"
	@echo "    logs-nginx    Follow Nginx logs"
	@echo ""
	@echo "  Dev"
	@echo "    shell         Open shell in PHP container"
	@echo "    composer      Run composer command (e.g. make composer require foo/bar)"
	@echo "    phpcs         Run PHP CodeSniffer"
	@echo "    phpcs-fix     Run PHP CodeSniffer fixer"
	@echo "    phpstan       Run PHPStan analysis"
	@echo ""

up:
	$(DC) up -d

start:
	$(DC) start

down:
	$(DC) down

restart:
	$(DC) restart

build:
	$(DC) up -d --build

logs:
	$(DC) logs -f

logs-php:
	$(DC) logs -f php

logs-nginx:
	$(DC) logs -f nginx

ps:
	$(DC) ps

stop:
	$(DC) stop

shell:
	$(DC) exec php sh

composer:
	$(DC) exec php composer $(filter-out $@,$(MAKECMDGOALS))

phpcs:
	$(DC) exec php vendor/bin/phpcs

phpcs-fix:
	$(DC) exec php vendor/bin/phpcbf

phpstan:
	$(DC) exec php vendor/bin/phpstan analyse

%:
	@:
