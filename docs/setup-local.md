# Setting up yout local enviroment

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
* `Node` to work with Vue.js

```
sudo apt-get install phpunit composer ruby nodejs npm
sudo gem install sass
```

* How to install WP-Cli check [here](https://wp-cli.org/#installing).


## Setting up

First of all, clone this repository.

Note that you can NOT clone it directly in the WordPress `plugins` directory. Clone it in a folder of its own and configure your build to point to the your local WordPress `plugins`
folder.

```
git clone git@git.github.com:tainacan/tainacan.git
```

Install dependencies via composer

```
composer install
npm install
```

And that's pretty much all. Now you will want to set up your build and your tests.

## Build

When we want to build the plugin, we run `build.sh` that basically compiles all the assets (sass), installs any dependence and moves the files to the plugin directory. This compiled version of the plugin will be then added to the official WordPress Plugin repository.

```
./build.sh
```

In order to use it, make a copy of `build-config-sample.sh` and name it only `build-config.sh`. Edit and set the path to the Wordpress installation, the URL to access Wordpress, and the path to the default Wordpress plugins folder where the build will be done.

If you are running a local environment, you might want to check `build-watch.sh`. This script will watch your development folder for changes and automatically build the plugin so you don't have to do it manually every time you modify a file.

## Tests

To execute all the tests, simply execute the `run-tests` script. But first you need to configure the testing frameworks separately. We used `PHPUnit` to check the unit tests and `Cypress.io` to check the integration and acceptance tests.

#### Configuration Cypress.io

To run the integration and acceptance tests is simple, because WP-Cli will create a test environment in the same Wordpress used for the development of tainacan, according to the configurations provided in `build-config.sh`. And so just run the script:

```
./run-cypress.sh
```

#### Configuration PHPUnit
To run the unit tests it is necessary to create a new MySQL database for your unit tests. This database will be cleaned and restored every time you run PHPUnit.

Install the WordPress test library by running the script provided in:

```
tests/bin/install-wp-tests.sh wordpress_test root root /path/to/wordpress-test-folder localhost latest
```
The parameters are:

* Database name
* MySQL username
* MySQL password
* WordPress Test Directory (In order to test the API, this folder must be inside your local webserver)
* MySQL host
* WordPress version
* Optional: skip create database

`WordPress Test Directory` will be created with 2 sub folders:

* `wordpress-test` - An installation of WordPress
* `wordpress-tests-lib` - As the name says, the WordPress Tests Library

Inside `tests` folder, edit the file called `bootstrap-config-sample.php` and inform the folder where you installed your WordPress Test Library. This will be `/path/to/wordpress-test-folder/wodpress-tests-lib`. Save the file as `bootstrap-config.php`.

Also note that the installation script will create a config file in the destination folder with your database credentials. If you have to change it, you will need to edit it there. And so just run the script:

```
phpunit
```

###  You are done! Now, to run your tests, simply go to the root directory of the repository and type:

```
./run-tests.sh
```
