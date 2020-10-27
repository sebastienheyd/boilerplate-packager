.PHONY:help test cs csfix clean
.DEFAULT_GOAL=help

help:
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

composer.phar:
	@curl -sS https://getcomposer.org/installer | php -- --filename=composer.phar
	@chmod +x composer.phar

vendor: composer.json
	@./composer.phar install --prefer-source --no-interaction --optimize-autoloader

cs: composer.phar vendor ## Check for coding standards
	@php vendor/bin/phpcs

csfix: composer.phar vendor ## Check and fix for coding standards
	@php vendor/bin/phpcbf

test: composer.phar vendor phpunit.xml ## Launch test
	@php vendor/bin/phpunit --coverage-text --coverage-clover=coverage.clover

clean: ## Remove files needed for tests
	@rm -rf report testbench vendor .phpunit.result.cache composer.lock composer.phar
