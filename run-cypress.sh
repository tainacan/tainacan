#!/bin/bash

if [ $# - lt 2 ]; then
	echo "usage: $0 [skip-head]"
	exit 1
fi

SKIP_HEAD=${1-1}

source build-config.cfg

./build.sh

plugin_name=`basename $wp_plugin_dir`
test_db_prefix='wptests_'

################## set up cypress environment
# replace table prefix in wp_config
echo "Changing DB prefix in wp_config..."
sed -i s/"require_once(ABSPATH . 'wp-settings.php');"/"\$table_prefix  = '$test_db_prefix';\nrequire_once(ABSPATH . 'wp-settings.php');"/ $wp_base_dir/wp-config.php

# install WordPress
cd $wp_base_dir
echo "Installing WordPress..."
wp core install --url=$wp_url --title=Test --admin_user=admin --admin_password=admin --admin_email=admin@admin.com

# Activate Tainacan
wp plugin activate $plugin_name

# back to tainacan dev directory
cd -

# cypress beginning
if [ ${SKIP_HEAD} = "0" ]
then
  ./node_modules/.bin/cypress run --config baseUrl=$wp_url
else
  ./node_modules/.bin/cypress open --config baseUrl=$wp_url
fi

################## teardown cypress environment
# remove WordPress
echo "Removing WordPress..."
cd $wp_base_dir
wp db query "DROP TABLES $(wp db tables | paste -s -d, -);"

# recover table prefix in wp_config
echo "Restoring DB prefix in wp_config..."
sed -i s/"\$table_prefix  = '$test_db_prefix';"/""/ $wp_base_dir/wp-config.php
