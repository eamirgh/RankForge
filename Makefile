.PHONY: help install test test-unit test-feature benchmark docs-dev docs-build docs-preview clean

help: ## Display this help screen
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-18s\033[0m %s\n", $$1, $$2}'

install: ## Install composer and npm dependencies
	composer install
	npm install

test: ## Run the complete Pest test suite
	./vendor/bin/pest

test-unit: ## Run only unit tests
	./vendor/bin/pest tests/Unit

test-feature: ## Run only feature tests
	./vendor/bin/pest tests/Feature

benchmark: ## Run the local performance benchmark suite
	php benchmarks/benchmark.php

mcp: ## Start the RankForge MCP server over stdio
	php artisan rankforge:mcp

docs-dev: ## Start VitePress documentation development server
	npm run docs:dev

docs-build: ## Build VitePress documentation for production
	npm run docs:build

docs-preview: ## Preview built documentation locally
	npm run docs:preview

clean: ## Clean cache and build artifacts
	rm -rf .phpunit.cache docs/.vitepress/dist node_modules/.vitepress/cache
