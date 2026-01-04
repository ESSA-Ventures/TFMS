.PHONY: up down dev perms

COMPOSE ?= docker compose

up:
	$(COMPOSE) up -d --build

down:
	$(COMPOSE) down

dev: up
	$(COMPOSE) exec db mysqladmin --silent --wait=30 -utfms -ptfms ping
	$(COMPOSE) exec app composer install --no-interaction --ignore-platform-req=php
	$(COMPOSE) exec app php artisan key:generate --force
	$(COMPOSE) exec app sh -c "rm -f database/schema/mysql-schema.dump"
	$(COMPOSE) exec \
		-e DB_CONNECTION=mysql \
		-e DB_HOST=db \
		-e DB_PORT=3306 \
		-e DB_DATABASE=tfms \
		-e DB_USERNAME=tfms \
		-e DB_PASSWORD=tfms \
		-e MYSQL_SSL_MODE=DISABLED \
		app php artisan migrate --force
	$(COMPOSE) exec node npm install
	$(COMPOSE) exec node npm run dev

perms:
	$(COMPOSE) exec -T app chown -R www-data:www-data storage bootstrap/cache
	$(COMPOSE) exec -T app chmod -R ug+rw storage bootstrap/cache
