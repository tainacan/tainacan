#!/bin/bash

if [ $# - lt 2 ]; then
	echo "usage: $0 [skip-head]"
exit 1
fi

SKIP_HEAD=${1-1}

source build-config.cfg

./build.sh

plugin_tainacan=`basename $wp_plugin_dir`
plugin_wpclear='wpress-db-cleaner'

test_db_prefix='wptests_'
cy_record_key='a15e6599-d240-4d98-b582-dea93a3008c0'

################# set up cypress environment
# replace table prefix in wp_config
echo "Changing DB prefix in wp_config..."
sed -i s/"require_once(ABSPATH . 'wp-settings.php');"/"\$table_prefix  = '$test_db_prefix';\nrequire_once(ABSPATH . 'wp-settings.php');"/ $wp_base_dir/wp-config.php

# install WordPress
cd $wp_base_dir
echo "Installing WordPress..."
wp core install --url=$wp_url --title=Test --admin_user=admin --admin_password=admin --admin_email=admin@admin.com

# Activate Tainacan
wp plugin activate $plugin_tainacan $plugin_wpclear

# back to tainacan dev directory
cd -

# cypress beginning
if [ ${SKIP_HEAD} = "0" ]
then
 ./node_modules/.bin/cypress run --config baseUrl=$wp_url --record --key $cy_record_key
else
 ./node_modules/.bin/cypress open --config baseUrl=$wp_url
fi

################## teardown cypress environment
# remove WordPress
echo "Removing WordPress..."
cd $wp_base_dir/
wp db query "DROP TABLES $(wp db tables | paste -s -d, -);"

# recover table prefix in wp_config
echo "Restoring DB prefix in wp_config..."
sed -i s/"\$table_prefix  = '$test_db_prefix';"/test/ $wp_base_dir/wp-config.php
sed -i '/test/d' $wp_base_dir/wp-config.php
