# Getting Started

This project structure contains the docker configurations and directory structure for a laravel project. It has an nginx, php and mysql container set up. This project is designed for minimal set up to make it quick and easy to get started.

## Setup (Unix and Mac)

1) If you don't have a project ready to link and want to create one, you can with the set up script. Just create a new empty directory and set up the symlink as you would with an existing laravel project. Once your application directory is ready, create the symlink as follows:

`ln -s /path/to/laravel/project /path/to/docker/project/root/app`

2) Once the link is set up, run the setup script which will handle the initial setup. The script takes a database root password, db user password that is assigned to the laraveluser and an unused string. The last string will trigger a new project build in the linked directory.

`./setup.sh [MYSQL_ROOT_PASSWORD] [LARAVEL_USER_PASSWORD] [buildstring or ""]`

## Setup (Windows)

Coming Soon


The project should now be running and showing up on http://localhost/