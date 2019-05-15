# Setting up your local enviroment

This document will run you through setting up your local enviroment and running the tests. If you haven't done it yet, please have a look at [key concepts](key-concepts.md) so you can have a better Understanding of the project.

## Overview

This is the development repository for the Tainacan WordPress plugin.

Overview of folders:

* `docs` - This technical documentation
* `src` - The actual plugin. Everything outside this folder is not part of the distribution package
* `tests` - phpunit tests
* `cypress` - integration tests

This repository includes all the tools needed to develop Tainacan, such as tests and other scripts to compile sass and other things.

## Before you start

Tainacan is a WordPress plugin, so you will need all the basic dependencies you usually have to run a WordPress site, such as PHP and MySQL.

You wil also need:

* `Composer` to manage dependencies
* `Sass` to compile sass into css files
* `WP-Cli` to configure the test environment
* `Phpunit` to run unit tests
* `Node` to handle dependencies and vuild the JS application

```
sudo apt-get install phpunit composer ruby ruby-dev nodejs npm
sudo gem install sass
```

* To install WP-Cli, check [the official documentation](https://wp-cli.org/#installing).


## Setting up

First of all, clone this repository.

Note that you can NOT clone it directly in the WordPress `plugins` directory. Clone it in a folder of its own and configure your build to point to  your local WordPress `plugins` folder.

```
git clone git@git.github.com:tainacan/tainacan.git
```

Set up a WordPress installation. This could be a dedicated installation to develop tainacan or you can use an existing instance you have. Its up to you, but you will need one, both for developing and manually testing, as well to run automated integration tests.

## Build

When we want to build the plugin, we run `build.sh` that basically installs any dependencies, compiles all the assets (sass and js) and moves the files to the plugin directory. This compiled version of the plugin is the one added to the official WordPress Plugin repository.

In order to use it, make a copy of `build-config-sample.cfg` and name it only `build-config.cfg`. Edit and fill in your environment details:

* `wp_base_dir`: The base directory for you local WordPress installation, used for development and testing. e.g `~/develop/wordpress`
* `wp_url`: The base URL for your local WordPress installation/ e.g `http://localhost/wp`
* `wp_plugin_dir`: The directory for your plugin build. Should be a directory inside `wp_base_dir`. e.g `~/develop/wordpress/wp-content/plugins/test-tainacan`

Once you are ready, you can run:

```
./build.sh
```

While developing, you might want to run `build-watch.sh`. This script will watch your development folder for changes and automatically build the plugin so you don't have to do it manually every time you modify a file.

## Tests

Tainacan uses `phpunit` to run tests for the backend and the API. This is a very important part of the development proccess! Never commit anything before run all the tests to make sure you did not break anything. If you are developing a new feature, you must write tests for it. If you are fixing a bug, you should first write a test that reproduces the bug and then make it pass.

To execute all the tests, simply execute the `phpunit` command from the project root folder. But first you need to configure PHPUnit.

#### Preparing PHPUnit

To run the unit tests it is necessary to create a new MySQL database for your unit tests. This database will be cleaned and restored every time you run PHPUnit.

Install the WordPress test library by running the script provided in the `tests/bin` folder, by running the following command:

```
tests/bin/install-wp-tests.sh wordpress_test root root /path/to/wordpress-test-folder localhost latest
```
The parameters are:

* Database name
* MySQL username
* MySQL password
* WordPress Test Directory*
* MySQL host
* WordPress version
* Optional: skip create database

\* `WordPress Test Directory` will be created with 2 sub folders:

* `wordpress-test` - An installation of WordPress
* `wordpress-tests-lib` - As the name says, the WordPress Tests Library

Inside `tests` folder, edit the file called `bootstrap-config-sample.php` and inform the folder where you installed your WordPress Test Library. This will be `/path/to/wordpress-test-folder/wodpress-tests-lib`. Save the file as `bootstrap-config.php`.

Note that the installation script will create a config file in the destination folder with your database credentials. If you have to change it, you will need to edit it there.

You only need to do all this once, and now you are ready to run tests.

#### Running tests

Simply type this command from the project root folder:

```
phpunit
```

(Note that `phpunit` accpets several parametrs, for example if you want to run just a specific group of tests).

