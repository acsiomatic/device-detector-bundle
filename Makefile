.DEFAULT_GOAL := help

help: ## Display this message help
	@make -v | head -n 1
	@awk '\
		BEGIN {\
			FS = ":.*##";\
			printf "\n\033[33mUsage:\033[0m\n  make [target]\n\n\033[33mAvailable targets:\033[0m\n" \
		} /^[a-zA-Z0-9_-]+:.*?##/ { \
			printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2 \
		} /^##/ { \
			printf "\033[33m %s\033[0m\n", substr($$0, 4) \
		}' $(MAKEFILE_LIST)
.PHONY: help

## Checks

check: phpstan-check rector-check cs-check phpunit ## Run all checks

phpstan-check: ## Run PHP Static Analysis
	vendor/bin/phpstan analyse --verbose
.PHONY: phpstan-check

rector-check: ## Check for Rector coding standards violations
	vendor/bin/rector process --dry-run --no-progress-bar
.PHONY: rector-check

cs-check: ## Check for coding standards violations
	mkdir -p var
	vendor/bin/php-cs-fixer fix --dry-run --verbose
.PHONY: cs-check

phpunit: ## Run PHPUnit tests
	php -d pcov.enabled=1 ./vendor/bin/phpunit --testdox --coverage-text --coverage-html var/phpunit/code-coverage-html --verbose
.PHONY: phpunit

## Fixers

fix: rector-fix cs-fix ## Run all fixers
.PHONY: fix

rector-fix: ## Fix Rector coding standards violations
	vendor/bin/rector process --no-progress-bar
.PHONY: rector-fix

cs-fix: ## Fix coding standards violations
	mkdir -p var
	vendor/bin/php-cs-fixer fix --verbose
.PHONY: cs-fix

## Misc

clean: ## Clean up workspace
	rm -rf composer.lock var/ vendor/
.PHONY: clean
