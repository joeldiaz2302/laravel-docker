#!/bin/bash

if [ $# -gt 0 ]; then
	echo "setting password values"
	ex -s +%s/MYSQL_ROOT_PASSWORD_VALUE/$1/ge -cwq docker-compose.yml
	ex -s +%s/LARAVEL_USER_PASSWORD/$2/ge -cwq mysql/setup.sql
	echo "docker-compose build"
	docker-compose build
	echo "docker-compose  up -d"
	docker-compose up -d
	echo "Waiting a few seconds to let the containers load up."
	sleep 8s
	echo "docker ps - Showing the created containers"
	docker ps

	if [ "$3" != "" ]; then
		echo "docker exec app composer create-project --prefer-dist laravel/laravel tempapp"
		docker exec app composer create-project --prefer-dist laravel/laravel tempapp
		echo "docker exec app rsync -vua --delete-after tempapp/ ."
		docker exec app rsync -vua --delete-after tempapp/ .
	fi
	
	echo "list of databases"
	docker exec db mysql -u root -p"$1" -e "show databases;"

	echo "docker exec app composer install"
	docker exec app composer install
	echo "exec app php artisan key:generate"
	docker exec app php artisan key:generate
	echo "exec app php artisan config:cache"
	docker exec app php artisan config:cache
	echo "Pausing setup. Configure application with database connection for migration setup. if you want to skip this step, enter s or S:"

	read  -n 1 -p "" mainmenuinput

	echo ""

	if [ "$mainmenuinput" != "s" ] && [ "$mainmenuinput" != "S" ]; then
		echo "exec app php artisan migrate"
		docker exec app php artisan migrate
	fi

	echo "unsetting password values"
	ex -s +%s/$1/MYSQL_ROOT_PASSWORD_VALUE/ge -cwq docker-compose.yml
	ex -s +%s/$2/LARAVEL_USER_PASSWORD/ge -cwq mysql/setup.sql
	echo "setup complete"
else
    echo "Your command line contains no arguments"
    exit 1
fi
