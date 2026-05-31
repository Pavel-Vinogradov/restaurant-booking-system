.PHONY: help build up down restart install composer-update key migrate lint analyse quality test logs shell php npm clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build Docker images
	docker-compose build

up: ## Start Docker containers
	docker-compose up -d

down: ## Stop Docker containers
	docker-compose down

restart: down up ## Restart Docker containers

install: build up composer-update key migrate ## Full project setup
	@echo "Project installed successfully!"
	@echo "App: http://localhost:8080"

composer-update: ## Update Composer dependencies
	docker-compose run --rm composer update

composer-install: ## Install Composer dependencies
	docker-compose run --rm composer install

key: ## Generate application key
	docker-compose exec php php artisan key:generate

migrate: ## Run database migrations
	docker-compose exec php php artisan migrate

migrate-fresh: ## Fresh migration with seeding
	docker-compose exec php php artisan migrate:fresh --seed

migrate-rollback: ## Rollback last migration
	docker-compose exec php php artisan migrate:rollback

lint: ## Run code linter (Laravel Pint)
	docker-compose exec php composer lint

lint-fix: ## Fix code style issues (Laravel Pint)
	docker-compose exec php composer lint:fix

analyse: ## Run static analysis (PHPStan)
	docker-compose exec php composer analyse

md: ## Run PHP Mess Detector
	docker-compose exec php composer md

quality: lint analyse md ## Run all quality checks

test: ## Run tests
	docker-compose exec php composer test

test-coverage: ## Run tests with coverage
	docker-compose exec php php artisan test --coverage

logs: ## Show logs from all containers
	docker-compose logs -f

logs-php: ## Show PHP container logs
	docker-compose logs -f php

logs-nginx: ## Show Nginx container logs
	docker-compose logs -f nginx

logs-postgres: ## Show PostgreSQL container logs
	docker-compose logs -f postgres

shell: ## Open shell in PHP container
	docker-compose exec php bash

php: ## Run PHP command (usage: make php ARGS="artisan route:list")
	docker-compose exec php php $(ARGS)

artisan: ## Run artisan command (usage: make artisan ARGS="route:list")
	docker-compose exec php php artisan $(ARGS)

npm-install: ## Install NPM dependencies
	cd src && npm install

npm-run: ## Run NPM script (usage: make npm-run ARGS="dev")
	cd src && npm run $(ARGS)

npm-build: ## Build frontend assets
	cd src && npm run build

npm-dev: ## Run frontend dev server
	cd src && npm run dev

npm-lint: ## Run ESLint
	cd src && npm run lint

npm-lint-fix: ## Fix ESLint issues
	cd src && npm run lint:fix

npm-format: ## Format code with Prettier
	cd src && npm run format

ps: ## Show running containers
	docker-compose ps

clean: ## Remove all containers, volumes, and images
	docker-compose down -v --rmi all
	@echo "Project cleaned successfully!"

rebuild: clean install ## Clean and rebuild everything
