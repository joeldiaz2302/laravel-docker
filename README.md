# Getting Started

## Set up docker containers
1) Move into the project directory `cd [project_dir]/app`

2) Run a temporary container to set up the php project `docker run --rm -v $(pwd) composer install`

3) Move back up to the project directory `cd ..`

4) Create the docker containers `docker-compose up -d`

5) Check that the containers are running and up `docker ps`

6) Duplicate the [project_dir]/app/.env.example file as .env

7) Set the database configuration

```conf
	DB_CONNECTION=mysql
	DB_HOST=db
	DB_PORT=3306
	DB_DATABASE=laravel
	DB_USERNAME=laraveluser
	DB_PASSWORD=your_laravel_db_password
```

8) Generate an application key `docker-compose exec app php artisan key:generate`

9) Configure the config cache `docker-compose exec app php artisan config:cache`

10) If all is working, check http://localhost and you should see the application

## Configure Database

1) Open a terminal into the db container `docker-compose exec db bash`

2) Log into the database as the root user `mysql -u root -p` using the password you set up in the docker-compose.yml under `db: MYSQL_ROOT_PASSWORD`

3) Check for the laravel database
```mysql
show databases;
```

which should look like this
```
+--------------------+
| Database           |
+--------------------+
| information_schema |
| laravel            |
| mysql              |
| performance_schema |
| sys                |
+--------------------+
5 rows in set (0.00 sec)

```

4) Set up the db user the application will use
```mysql
GRANT ALL ON laravel.* TO 'laraveluser'@'%' IDENTIFIED BY 'your_laravel_db_password';
```

5) Flush privkeges `FLUSH PRIVILEGES;`

6) exit the terminals

## Run the migrations
1) `docker-compose exec app php artisan migrate`

