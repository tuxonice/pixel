DC = docker compose

.PHONY: up start down restart build logs logs-php logs-nginx shell composer ps stop phpcs phpcs-fix phpstan

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
