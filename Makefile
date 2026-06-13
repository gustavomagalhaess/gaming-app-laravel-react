.DEFAULT_GOAL := help

DC = docker compose

.PHONY: help
help: ## Show this help.
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage: make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

.PHONY: init
init: ## Copy .env.example to .env if missing.
	@test -f .env || cp .env.example .env

.PHONY: build
build: init ## Build all Docker images.
	$(DC) build

.PHONY: install
install: build ## Bootstrap the application (create project + install deps + migrate + build assets).
	./scripts/install.sh

.PHONY: up
up: ## Start the application stack in the background.
	$(DC) up -d app web db

.PHONY: dev
dev: ## Start the stack with the Vite dev server (node container foreground).
	$(DC) --profile dev up app web db node

.PHONY: down
down: ## Stop and remove containers (preserves volumes).
	$(DC) --profile dev down

.PHONY: nuke
nuke: ## Stop containers and remove volumes (destroys DB data).
	$(DC) --profile dev down -v

.PHONY: shell
shell: ## Open a shell inside the PHP container.
	$(DC) exec app bash

.PHONY: artisan
artisan: ## Run an artisan command, e.g. `make artisan c="migrate --seed"`.
	$(DC) exec app php artisan $(c)

.PHONY: composer
composer: ## Run a composer command, e.g. `make composer c="require foo/bar"`.
	$(DC) exec app composer $(c)

.PHONY: npm
npm: ## Run an npm command in the app container, e.g. `make npm c="run build"`.
	$(DC) exec app npm $(c)

.PHONY: test
test: ## Run the PHPUnit test suite inside the container.
	$(DC) exec app php artisan test

.PHONY: migrate
migrate: ## Run database migrations.
	$(DC) exec app php artisan migrate

.PHONY: fresh
fresh: ## Drop all tables, re-migrate, and re-seed.
	$(DC) exec app php artisan migrate:fresh --seed

.PHONY: logs
logs: ## Tail logs from all containers.
	$(DC) logs -f

.PHONY: pint
pint: ## Run Laravel Pint code style fixer. `make pint c="--repair"` to auto-fix issues.
	$(DC) exec app ./vendor/bin/pint $(c)
