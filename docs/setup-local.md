# Setting up your local environment

This document will run you through setting up your local environment and running the tests. If you haven't done it yet, please have a look at [key concepts](key-concepts.md) so you can have a better understanding of the project.

## Where the plugin code lives

The code you edit is the `src/` directory of the [plugin source](https://github.com/tainacan/tainacan). `build.sh` compiles the assets and copies what WordPress needs into the plugins directory. That copy is most of `src/`, with `tainacan.php` as the main file. The script leaves out files that are only used to build, such as the Vue, Sass, and JavaScript sources under `views/`. A production build leaves out more, including source maps and a few legacy PHP classes.

The build scripts, the tests, and the source of this guide stay outside `src/`. `src/vendor/`, `src/assets/js/`, and `src/assets/css/` are written by the build. Edit the PHP, Vue, JavaScript, or Sass sources. The next build rewrites those three directories and the copy in the plugins directory.

## With Docker

This is the setup to use when you do not already have a WordPress install of your own. You need Docker with Compose v2, `make`, and Git.

Clone the plugin and the stack as sibling directories. The stack looks for the plugin at `../tainacan`.

```bash
git clone https://github.com/tainacan/tainacan.git
git clone https://github.com/tainacan/tainacan-docker.git
cd tainacan-docker
make setup
```

`make setup` builds the assets, starts MariaDB, WordPress, and nginx, installs WordPress, and activates Tainacan. Open <http://localhost:8080> and sign in with the user and password in `.env`. The defaults are `tainacan` / `tainacan`.

If your plugin checkout is not a sibling of `tainacan-docker`, set `TAINACAN_PLUGIN_PATH` in `.env` to the plugin repository root, the directory that contains `build.sh`. The stack appends `/src` itself and mounts that directory read-only into WordPress. Do not clone the plugin into `wp-content/plugins`. WordPress core stays in a Docker volume, not on your disk.

PHP changes show up on refresh. JavaScript, Vue, and Sass do not. In a second terminal, from `tainacan-docker`:

```bash
make up       # start the stack when it is stopped
make watch    # rebuild assets when those files change
```

`make assets` builds once and exits. Leave `build-config.cfg` alone. `make setup` and `make assets` write it with the paths the containers use.

### Tests

From `tainacan-docker`:

```bash
make test
```

The first run installs the WordPress test library inside the builder container and then runs PHPUnit. The database is the stack's MariaDB, not a MySQL server on your machine.

### Your first change

Create the branch from `develop` inside the plugin clone, not inside `tainacan-docker`:

```bash
cd ../tainacan
git checkout develop
git pull
git checkout -b fix/short-description
```

Edit files under `src/`, except the generated directories listed above. Run `make test` from `tainacan-docker` before you push. Commit messages and code comments are in English. See [contribution guidelines](CONTRIBUTING.md).

If the stack itself fails, see the [troubleshooting section](https://github.com/tainacan/tainacan-docker#troubleshooting ":ignore") of the Docker repository. That is where a blank admin, permalinks that 404, and an import, export, or bulk edit stuck on `waiting` are explained.

## Without Docker

Use this when you already run WordPress on the machine and want the build to copy the plugin into that install.

You need PHP, MySQL, Composer, Node.js and npm, WP-CLI, and Subversion. Subversion is what the WordPress test installer uses to download the test library. Sass comes from npm when `build.sh` runs. Do not install the Ruby `sass` gem.

```bash
sudo apt install php-cli php-gd php-mysql php-xml php-mbstring php-curl php-zip composer nodejs npm subversion
```

Install WP-CLI from [the official documentation](https://wp-cli.org/#installing).

Clone the plugin on its own. Do not clone it into `wp-content/plugins`. The build copies a compiled tree into the plugins directory for you.

```bash
git clone https://github.com/tainacan/tainacan.git
cd tainacan
```

Copy `build-config-sample.cfg` to `build-config.cfg` and set:

- `wp_base_dir`: your WordPress directory, for example `~/develop/wordpress`
- `wp_url`: its URL, for example `http://localhost/wp`
- `wp_plugin_dir`: a directory inside that install's `wp-content/plugins`, for example `~/develop/wordpress/wp-content/plugins/tainacan`

Then build:

```bash
./build.sh
```

`./build-watch.sh` rebuilds when source files change.

### Tests

Create a MySQL database used only by PHPUnit. The test run empties it. Install the WordPress test library:

```bash
tests/bin/install-wp-tests.sh wordpress_test root root /path/to/wordpress-test-folder localhost latest
```

The arguments are the database name, MySQL user, MySQL password, the directory that will hold the test library, the MySQL host, and the WordPress version. A seventh argument skips creating the database.

That directory receives two folders: `wordpress-test`, a WordPress install, and `wordpress-tests-lib`, the test library.

Copy `tests/bootstrap-config-sample.php` to `tests/bootstrap-config.php` and set `tests_dir` to `/path/to/wordpress-test-folder/wordpress-tests-lib`. The installer also writes database credentials into `wordpress-tests-lib/wp-tests-config.php`. Change them there if you need to.

On Linux with XAMPP, `localhost` makes PHP use a Unix socket. Open `wp-tests-config.php` and set the host to `127.0.0.1` so PHP connects over TCP:

```php
define( 'DB_HOST', '127.0.0.1' );
```

Run the suite from the plugin root:

```bash
src/vendor/bin/phpunit
```

`phpunit` accepts a `--filter` for a single class or method. You only install the library once. Run the tests before every contribution. A bug fix starts from a test that fails for that bug. A new feature needs tests that cover it.
