# no buildin rules and variables
MAKEFLAGS =+ -rR --warn-undefined-variables

.PHONY: composer-install composer-update phpstan cs-fixer examples docker run

CONFLUENT_VERSION ?= latest
CONFLUENT_NETWORK_SUBNET ?= 172.68.0.0/24
SCHEMA_REGISTRY_IPV4 ?= 172.68.0.103
KAFKA_BROKER_IPV4 ?= 172.68.0.102
ZOOKEEPER_IPV4 ?= 172.68.0.101
COMPOSER ?= bin/composer.phar
COMPOSER_VERSION ?= 2.1.5
PHP_STAN ?= bin/phpstan.phar
PHP_STAN_VERSION ?= 0.12.94
PHP_CS_FIXER ?= bin/php-cs-fixer.phar
PHPUNIT ?= vendor/bin/phpunit
PHP ?= bin/php
PHP_VERSION ?= 7.3
XDEBUG_VERSION ?= 3.0.4
XDEBUG_OPTIONS ?= -d xdebug.mode=coverage -d xdebug.coverage_enable=0
export

docker:
	docker build \
	  --build-arg PHP_VERSION=$(PHP_VERSION) \
	  --build-arg XDEBUG_VERSION=$(XDEBUG_VERSION) \
	  -t schema-registry-client:$(PHP_VERSION) \
	  -f Dockerfile \
	  .

composer-install:
	PHP_VERSION=$(PHP_VERSION) $(PHP) $(XDEBUG_OPTIONS) $(COMPOSER) install --no-interaction --no-progress --no-scripts --prefer-stable

composer-update:
	PHP_VERSION=$(PHP_VERSION) $(PHP) $(XDEBUG_OPTIONS) $(COMPOSER) update --no-interaction --no-progress --no-scripts --prefer-stable

phpstan:
	PHP_VERSION=$(PHP_VERSION) $(PHP) $(XDEBUG_OPTIONS) $(PHP_STAN) analyse

cs-fixer:
	PHP_VERSION=$(PHP_VERSION) $(PHP) $(XDEBUG_OPTIONS) $(PHP_CS_FIXER) fix --config=.php_cs.dist --diff -v --dry-run \
	  --path-mode=intersection --allow-risky=yes src test

cs-fixer-modify:
	PHP_VERSION=$(PHP_VERSION) $(PHP) $(XDEBUG_OPTIONS) $(PHP_CS_FIXER) fix --config=.php_cs.dist --diff -v \
	  --path-mode=intersection --allow-risky=yes src test

phpunit:
	PHP_VERSION=$(PHP_VERSION) $(PHP) $(XDEBUG_OPTIONS) $(PHPUNIT) --exclude-group integration

phpunit-integration:
	PHP_VERSION=$(PHP_VERSION) $(PHP) $(XDEBUG_OPTIONS) $(PHPUNIT) --group integration

coverage:
	mkdir -p build
	PHP_VERSION=$(PHP_VERSION) $(PHP) -d xdebug.mode=coverage -d xdebug.coverage_enable=1 vendor/bin/phpunit --exclude-group integration \
	  --coverage-clover=build/coverage.clover --coverage-text

ci-local: cs-fixer phpstan phpunit

examples:
	PHP_VERSION=$(PHP_VERSION) $(PHP) examples/*

install-phars:
	curl https://cs.symfony.com/download/php-cs-fixer-v2.phar -o bin/php-cs-fixer.phar -LR -z bin/php-cs-fixer.phar
	chmod a+x bin/php-cs-fixer.phar
	curl https://getcomposer.org/download/$(COMPOSER_VERSION)/composer.phar -o bin/composer.phar -LR -z bin/composer.phar
	chmod a+x bin/composer.phar
	curl https://github.com/phpstan/phpstan/releases/download/$(PHP_STAN_VERSION)/phpstan.phar -o bin/phpstan.phar -LR -z bin/phpstan.phar
	chmod a+x bin/phpstan.phar

platform:
	docker-compose down
	docker-compose up -d
	bin/wait-for-all.sh

clean:
	rm -rf build
	docker-compose down
