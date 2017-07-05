
quick-test:
	./vendor/bin/phpunit -v --exclude-group integration --coverage-text

integration-test:
	docker-compose up -d
	sleep 10
	./vendor/bin/phpunit -c phpunit.xml.integration.dist -v --group integration
	docker-compose down

clean:
	docker-compose down
