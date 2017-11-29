# Setting up yout local enviroment

This document will run you through setting up your local enviroment and running the tests. If you haven't done it yet, please have a look at [key concepts](key-concepts.md) so you can have a better Understanding of the project.

## Overview

This is the development repository for the Tainacan WordPress plugin.

Overview of folders:

* `docs` - This tecnhinal documentation
* `tests` - phpunit tests
* `src` - The actual plugin. Everything outside this folder is not part of the distribution package

This repository includes all the tools needed to develop Tainacan, such as tests and other scripts to compile sass and other things.

## Before you start

Tainacan is a WordPress plugin, so you will need all the basic dependencies you usually have to run a WordPress site, such as PHP and MySQL.

You wil also need:

* `composer` to manage dependencies
* `sass` to compile sass into css files
* `phpunit` to run tests

```
sudo apt-get install phpunit composer ruby
sudo gem install sass
```

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
```

And that's pretty much all. Now you will want to set up your tests and your build.

## Build

When we want to build the plugin, we run `build.sh` that basically compiles all the assets (sass), installs any dependence and moves the files to the plugin directory. This compiled version of the plugin will be then added to the official WordPress Plugin repository.

In order to use it, make a copy of `build-config-sample.sh` and name it only `build-config.sh`. Edit it and set the folder in which you want the build to be made.

If you are running a local enviroment, you might want to check `build-watch.sh`. This script will watch your development folder for changes and automatically build the plugin so you dont have to do it manually every time you modify a file.

## Tests

Create a new MySQL database for your tests. This database will be cleaned and restored everytime you run the tests.

Install the WordPress Tests library running the provided script.

```
tests/bin/install-wp-tests.sh wordpress_test root root /path/to/webserver/document/root localhost latest
```
The parameters are:

* Database name
* MySQL username
* MySQL password
* Test Directory (This should be your web server document root)
* MySQL host
* WordPress version

Inside `tests` folder, edit the file called `bootstrap-config-sample.php` and inform the folder where you installed your WordPress Test Library. Save the file as `bootstrap-config.php`.

##### To test the API

Go to the `wordpress-test-lib` directory located in your test directory, open the file `wordpress-tests-config.php` and change the <domain-root> (could be different) with your configs, like the following:

```
define( 'WP_TESTS_DOMAIN', '<domain-root>/wordpress-test/' );
define( 'WP_TESTS_EMAIL', 'test@<domain-root>' );
define( 'WP_TESTS_TITLE', 'Tainacan Tests' );
```

With it done, go to `wordpress-test` directory. Make a copy of the `wp-config-sample.php` to `wp-config.php`, open that new file and add the MySQL settings, which you informed on installation script.

Now go to the URL of the wordpress installation test (example: localhost/wordpress-test) and make the wordpress common installation.

Execute the build script, go to Wordpress plugins page and active Tainacan plugin.

Obs: Don't forget, the URL used in API tests should be the same in constant `WP_TESTS_DOMAIN`, of course, with the prefix `http://` (like http://localhost/wordpress-test/).

You are done! Now, to run your tests, simply go to the root directory of the repository and type:

```
phpunit
```

Also note that the installation script will create a config file in the destination folder with your database credentials. If you have to change it, you will need to edit it there.