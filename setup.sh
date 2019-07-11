#!/bin/bash

cp docker-compose.yml.template docker-compose.yml
ex -s +%s/DOCKER_ROOT/.\\//ge -cwq docker-compose.yml
if [ $# -gt 1 ]; then
	echo setting password values
	ex -s +%s/MYSQL_ROOT_PASSWORD_VALUE/$1/ge -cwq docker-compose.yml
	ex -s +%s/LARAVEL_USER_PASSWORD/$2/ge -cwq mysql/setup.sql
	echo docker-compose build
	docker-compose build
	echo docker-compose  up -d
	docker-compose up -d
	echo Waiting a few seconds to let the containers load up.
	sleep 4s
	echo docker ps - Showing the created containers
	docker ps
	sleep 4s

	if [ "$3" != "" ]; then
		echo docker exec app composer create-project --prefer-dist laravel/laravel tempapp
		docker exec app composer create-project --prefer-dist laravel/laravel tempapp
		echo docker exec app rsync -vua --delete-after tempapp/ .
		docker exec app rsync -vua --delete-after tempapp/ .
	fi
	
	echo configuring application .env file with necessary database connection configuration
	if grep -q DB_CHARSET= app/.env
	then
		ex -s +%s/DB_CONNECTION=.*/DB_CONNECTION=mysql/ge -cwq app/.env
		ex -s +%s/rDB_CHARSET=.*/DB_CHARSET=utf8/ge -cwq app/.env
	else
		ex -s +%s/DB_CONNECTION=.*/DB_CONNECTION=mysql\\rDB_CHARSET=utf8/ge -cwq app/.env
	fi
	ex -s +%s/DB_HOST=.*/DB_HOST=db/ge -cwq app/.env
	ex -s +%s/DB_DATABASE=.*/DB_DATABASE=laravel/ge -cwq app/.env
	ex -s +%s/DB_USERNAME=.*/DB_USERNAME=laraveluser/ge -cwq app/.env
	ex -s +%s/DB_PASSWORD=.*/DB_PASSWORD=$2/ge -cwq app/.env

	echo list of databases exec db mysql -u root -p$1 -e "show databases;"
	docker exec db mysql -u root -p$1 -e "show databases;"
	sleep 4s

	echo docker exec app composer install
	docker exec app composer install
	echo docker exec app php artisan key:generate
	docker exec app php artisan key:generate
	echo docker exec app php artisan config:cache
	docker exec app php artisan config:cache

	echo docker exec app php artisan migrate
	docker exec app php artisan migrate

	echo docker exec app php artisan db:seed
	docker exec app php artisan db:seed

	echo unsetting configuration values
	ex -s +%s/$1/MYSQL_ROOT_PASSWORD_VALUE/ge -cwq docker-compose.yml
	ex -s +%s/$2/LARAVEL_USER_PASSWORD/ge -cwq mysql/setup.sql
	echo setup complete
else
    echo Your command line contains no arguments
    exit 1
fi
