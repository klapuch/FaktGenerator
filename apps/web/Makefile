.DEFAULT_GOAL := check

PHPCS_CACHE_DIR := /tmp/cache
PHPCS_ARGS := --standard=ruleset.xml --extensions=php --encoding=utf-8 --cache=$(PHPCS_CACHE_DIR)/phpcs --tab-width=4 -sp App Tests

.PHONY: check
check: posix-line-check validate-composer.lock lint phpstan phpcs

.PHONY: posix-line-check
posix-line-check:   ## checks new line on end of the files
	@sh /var/www/faktgenerator/apps/tools/posix-line-check.sh all

.PHONY: ci
ci: check

.PHONY: help
help:               ## help
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

.PHONY: lint
lint:               ## lint
	vendor/bin/parallel-lint -e php,phpt App

.PHONY: phpstan
phpstan:            ## phpstan
	vendor/bin/phpstan analyse -c phpstan.neon App

.PHONY: phpcs
phpcs:              ## phpcs
	@mkdir -p $(PHPCS_CACHE_DIR)
	vendor/bin/phpcs $(PHPCS_ARGS)

.PHONY: phpcbf
phpcbf:             ## phpcbf
	@mkdir -p $(PHPCS_CACHE_DIR)
	vendor/bin/phpcbf $(PHPCS_ARGS)

.PHONY: validate-composer.lock
validate-composer.lock:
	composer validate --no-check-all --strict

.PHONY: composer-install
composer-install:
	composer install --no-interaction --prefer-dist --no-scripts --no-progress --no-suggest --classmap-authoritative

.PHONY: migration-create
migration-create:   ## create migration
	@sh db/postgres/migrations/create.sh

.PHONY: migration-run
migration-run:      ## run migration $NAME
	@sh db/postgres/migrations/run.sh $(name)

.PHONY: migration-run-new
migration-run-new:  ## run all new migrations
	@sh db/postgres/migrations/run-new.sh

.PHONY: migration-list-new
migration-list-new: ## list new migrations
	@sh db/postgres/migrations/list-new.sh

.PHONY: migration-diff
migration-diff:     ## diff structure
	sh db/postgres/migrations/diff.sh
