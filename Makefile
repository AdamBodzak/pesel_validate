.DEFAULT_GOAL := help

DOCKER_COMPOSE = docker compose
PHP = $(DOCKER_COMPOSE) exec php
NODE = $(DOCKER_COMPOSE) run --rm node

export HOST_UID := $(shell id -u)
export HOST_GID := $(shell id -g)

.PHONY: help build up down sh composer console test test-db npm assets

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker images
	$(DOCKER_COMPOSE) build

up: ## Start containers in the background
	$(DOCKER_COMPOSE) up -d

down: ## Stop and remove containers
	$(DOCKER_COMPOSE) down

sh: ## Open a shell in the PHP container
	$(PHP) sh

composer: ## Run Composer, e.g. make composer c="require symfony/uid"
	$(PHP) composer $(c)

console: ## Run Symfony console, e.g. make console c="about"
	$(PHP) bin/console $(c)

test: ## Run PHPUnit tests, e.g. make test c="--filter PeselTest"
	$(PHP) bin/phpunit $(c)

test-db: ## Create and migrate the test database
	$(PHP) bin/console doctrine:database:create --env=test --if-not-exists
	$(PHP) bin/console doctrine:migrations:migrate --env=test --no-interaction

npm: ## Run npm in a one-off node container, e.g. make npm c="install"
	$(NODE) npm $(c)

assets: ## Build frontend assets for production (the node service rebuilds them on change in dev)
	$(NODE) npm run build
