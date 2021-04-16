fixtures.dev:
	docker-compose exec php bin/console hautelook:fix:load -e dev

fixtures.test:
	docker-compose exec php bin/console hautelook:fix:load -e test
