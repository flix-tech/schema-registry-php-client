
quick-test:
	./vendor/bin/phpunit --exclude-group integration --coverage-text

integration-test:
	docker-compose up -d
	sleep 10
	./vendor/bin/phpunit --group integration
	docker-compose down

clean:
	docker-compose down
