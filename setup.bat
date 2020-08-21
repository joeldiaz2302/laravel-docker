@ECHO off

echo run the script to generate ssl certs first since it will load .env and if its run after this script will block access to it
echo create certs
cmd /C create_ssl.bat

echo load the environment variables

setlocal enableextensions enabledelayedexpansion

if EXIST ".env" (
  for /F "tokens=*" %%I in (.env) do @set %%I
)
echo DOCKER_LOCATION=%DOCKER_LOCATION%
echo PROJECT_LOCATION=%PROJECT_LOCATION%


echo build a docker instance for text editing script to run

docker pull node:10
echo docker run -dit -v "%DOCKER_LOCATION%":"%INSTALL_SRC%" -v "%PROJECT_LOCATION%/%PROJECT_NAME%":"%INSTALL_PROJ%" -w %INSTALL_SRC% --name nodeinstaller node
docker run -dit -v "%DOCKER_LOCATION%":"%INSTALL_SRC%" -v "%PROJECT_LOCATION%/%PROJECT_NAME%":"%INSTALL_PROJ%" -w %INSTALL_SRC% --name nodeinstaller node
docker exec nodeinstaller npm i

rem echo docker-compose build
rem docker-compose build
echo docker-compose  up -d --build
docker-compose up -d
echo Waiting a few seconds to let the containers load up.
timeout /t 4 
echo docker ps - Showing the created containers
docker ps
timeout /t 4

rem if [ "$NEW_PROJECT" == "build" ]; then
rem 	if [ "$PROJECT_REPO" != "" ]; then
rem 		echo docker exec app composer create-project --prefer-dist laravel/laravel tempapp --repository-url=$PROJECT_REPO
rem 		docker exec app composer create-project --prefer-dist laravel/laravel tempapp --repository-url=$PROJECT_REPO
rem 	else
rem 		echo docker exec app composer create-project --prefer-dist laravel/laravel tempapp 
rem 		docker exec app composer create-project --prefer-dist laravel/laravel tempapp
rem 	fi
rem 	echo docker exec app rsync -vua --delete-after tempapp/ .
rem 	docker exec app rsync -vua --delete-after tempapp/ .
rem fi

docker exec nodeinstaller npm run setupEnvFile
timeout /t 10

echo list of databases
docker exec db mysql -u root -p"%MYSQL_ROOT_PASSWORD%" -e "show databases;"
docker exec db mysql -u root -p"%MYSQL_ROOT_PASSWORD%" -e "GRANT ALL PRIVILEGES ON %MYSQL_GRANT% TO %MYSQL_USER% IDENTIFIED BY '%USER_PASSWORD%';"
docker exec db mysql -u root -p"%MYSQL_ROOT_PASSWORD%" -e "GRANT ALL PRIVILEGES ON %MYSQL_GRANT% TO %MYSQL_APPUSER% IDENTIFIED BY '%USER_PASSWORD%';"
docker exec db mysql -u root -p"%MYSQL_ROOT_PASSWORD%" -e "FLUSH PRIVILEGES;"
timeout /t 4


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
