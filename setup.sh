#!/bin/bash

echo load the environment variables

set -o allexport
source .env
set +o allexport

echo build a docker instance for text editing script to run

docker pull node:10
docker run -dit -v $DOCKER_LOCATION:$INSTALL_SRC -v $PROJECT_LOCATION:$INSTALL_PROJ -w $INSTALL_SRC --name nodeinstaller node
docker exec nodeinstaller npm i

echo docker-compose build
docker-compose build
echo docker-compose  up -d
docker-compose up -d
echo Waiting a few seconds to let the containers load up.
sleep 4s
echo docker ps - Showing the created containers
docker ps
sleep 4s

if [ "$NEW_PROJECT" == "build" ]; then
	if [ "$PROJECT_REPO" != "" ]; then
		echo docker exec app composer create-project --prefer-dist laravel/laravel tempapp --repository-url=$PROJECT_REPO
		docker exec app composer create-project --prefer-dist laravel/laravel tempapp --repository-url=$PROJECT_REPO
	else
		echo docker exec app composer create-project --prefer-dist laravel/laravel tempapp 
		docker exec app composer create-project --prefer-dist laravel/laravel tempapp
	fi
	echo docker exec app rsync -vua --delete-after tempapp/ .
	docker exec app rsync -vua --delete-after tempapp/ .
fi

docker exec nodeinstaller npm run setupEnvFile

echo list of databases
docker exec db mysql -u root -p$MYSQL_ROOT_PASSWORD -e "show databases;"
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
docker container stop nodeinstaller
docker container rm nodeinstaller
echo setup complete
