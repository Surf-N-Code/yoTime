fix.load.dev:
	docker-compose exec php bin/console hautelook:fix:load -e dev

fix.load.test:
	docker-compose exec php bin/console hautelook:fix:load -e test
