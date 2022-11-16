STEP = "\\n\\r**************************************************\\n"
PROJECT_NAME = marshal

## Tests
tests-coverage: composer-pre-test coverage
tests: composer-pre-test bin-phpunit

bin-phpunit:
	@echo "$(STEP) Exécution des tests... $(STEP)"
	-php -d memory_limit=-1 ./vendor/bin/phpunit --configuration phpunit.xml.dist

composer-pre-test:
	@echo "$(STEP) Préparation pour les tests... $(STEP)"
	@composer pre-test > /dev/null 2>&1

coverage:
	@echo "$(STEP) Exécution des tests avec analyse du coverage... $(STEP)"
	-XDEBUG_MODE=coverage php -d memory_limit=-1 ./vendor/bin/phpunit --configuration phpunit.xml.dist --log-junit docs/coverage/junit-report.xml --coverage-text --coverage-cobertura=docs/coverage/cobertura.xml --coverage-html docs/coverage

## Use
start:
	symfony proxy:start
	symfony proxy:domain:attach local.marshal.planete-croisiere
	docker-compose up -d
	@composer install
	@yarn install
	php bin/console doctrine:migrations:migrate -n
	@symfony server:ca:install
	symfony server:start -d
	yarn encore dev-server --port 7000

stop:
	docker-compose stop
	symfony server:stop

reinit-fixtures:
	rm -rf var/cache/*
	php bin/console doctrine:schema:drop --force
	php bin/console doctrine:schema:update --force
	php bin/console doctrine:fixtures:load --no-interaction

