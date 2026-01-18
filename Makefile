DC = docker compose
APP = app
NODE = node
COMPOSER_FLAGS = --ignore-platform-req=php

.PHONY: help dev build up down install key perms migrate seed fresh npm-install npm-dev npm-build bash logs

help:
	@echo "Targets: dev, build, up, down, install, key, perms, migrate, seed, fresh, npm-install, npm-dev, npm-build, bash, logs"

dev:
	$(DC) build $(APP)
	$(DC) run --rm $(APP) composer install $(COMPOSER_FLAGS)
	$(DC) run --rm $(APP) php artisan key:generate
	$(DC) run --rm $(APP) sh -c "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage/app storage/framework storage/logs bootstrap/cache"
	$(DC) up -d

build:
	$(DC) build $(APP)

up:
	$(DC) up -d

down:
	$(DC) down

install:
	$(DC) run --rm $(APP) composer install $(COMPOSER_FLAGS)

key:
	$(DC) run --rm $(APP) php artisan key:generate

perms:
	$(DC) run --rm $(APP) sh -c "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage/app storage/framework storage/logs bootstrap/cache"

migrate:
	$(DC) run --rm $(APP) php artisan migrate

seed:
	$(DC) run --rm $(APP) php artisan db:seed

fresh:
	$(DC) run --rm $(APP) php artisan migrate:fresh --seed

npm-install:
	$(DC) run --rm $(NODE) npm install

npm-dev:
	$(DC) run --rm $(NODE) npm run dev -- --watch

npm-build:
	$(DC) run --rm $(NODE) npm run build

bash:
	$(DC) exec $(APP) bash

logs:
	$(DC) logs -f
