# Getting Started

This project structure contains the docker configurations and directory structure for a laravel project. It has an nginx, php and mysql container set up. This project is designed for minimal set up to make it quick and easy to get started.

## Setup

1) If you don't have a project ready to link and want to set up with a new empty directory for the project. If you have a laravel project ready, just make the symlink as follows.

`ln -s /path/to/laravel/project /path/to/docker/project/root/app`

2) Once the link is set up, run the setup batch which will handle the majority of the setup. The script takes a database root password, db user password that is assigned to the laraveluser and an unused string. The last string will trigger a new project build in the linked directory.

`./setup.sh [MYSQL_ROOT_PASSWORD] [LARAVEL_USER_PASSWORD] [buildstring or ""]`

3) If you are building the application or using it in this project you need to set the database configuration with this replacing `[MYSQL_ROOT_PASSWORD]` with your password.

```conf
	DB_HOST=db
	DB_PORT=3306
	DB_CHARSET=utf8
	DB_DATABASE=laravel
	DB_USERNAME=laraveluser
	DB_PASSWORD=[MYSQL_ROOT_PASSWORD]
```

4) The last step is running the migrations for the project. You will be given a prompt to allow you to set the configuration on new projects or update on existing ones. If you don't want to run the migrations you can skip this step and run them later with:

`docker exec app php artisan migrate`

The project should now be running and showing up on http://localhost/