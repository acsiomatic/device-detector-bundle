ifeq ($(shell type podman > /dev/null 2>&1; echo $$?), 0)
	ENGINE ?= podman
else ifeq ($(shell type docker > /dev/null 2>&1; echo $$?), 0)
	ENGINE ?= docker
endif

PHP_VERSION ?= 7.4

RUN = $(ENGINE) run --init -it --rm -v "$(CURDIR):/project" -w /project jakzal/phpqa:php$(PHP_VERSION)

.DEFAULT_GOAL := help

help: ## Display this message help
	@make -v | head -n 1
	@awk '\
		BEGIN {\
			FS = ":.*##";\
			printf "\n\033[33mUsage:\033[0m\n  [PHP_VERSION=major.minor] make [target]\n\n\033[33mAvailable targets:\033[0m\n" \
		} /^[a-zA-Z0-9_-]+:.*?##/ { \
			printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2 \
		} /^##/ { \
			printf "\033[33m %s\033[0m\n", substr($$0, 4) \
		}' $(MAKEFILE_LIST)
.PHONY: help

## Checks

check: static-analysis cs-check test ## Run all checks

static-analysis: vendor ## Run static analysis
	$(RUN) phpstan analyse --verbose
.PHONY: static-analysis

cs-check: check-engine ## Check for coding standards violations
	mkdir -p var
	$(RUN) php-cs-fixer fix --dry-run --verbose
.PHONY: cs-check

test: vendor ## Run tests
	$(RUN) php -d pcov.enabled=1 ./vendor/bin/phpunit --testdox --coverage-text --verbose
.PHONY: test

## Fixers

cs-fix: check-engine ## Fix coding standards
	mkdir -p var
	$(RUN) php-cs-fixer fix
.PHONY: cs-fix

## Misc

clean: check-engine ## Clean up workspace
	$(RUN) rm -rf composer.lock var/ vendor/
.PHONY: clean

vendor: check-engine
	$(RUN) composer install -vvv

check-engine:
ifeq ($(ENGINE),)
	$(error "Container engine not found. Did you install podman or docker?")
endif
.PHONY: check-engine
