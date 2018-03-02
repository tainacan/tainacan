#!/bin/bash

source build-config.cfg

#./build.sh 


plugin_name=`basename $destination`
test_db_prefix='wp_tainacan_test_'


# set up cypress environment
# replace table prefix in wp_config
sed -i s/"require_once(ABSPATH . 'wp-settings.php');"/"\$table_prefix  = '$test_db_prefix';\nrequire_once(ABSPATH . 'wp-settings.php');"/ $wp_base_dir/wp-config.php 

# install WordPress
cd $wp_base_dir
wp core install --url=$wp_url --title=Test --admin_user=admin --admin_password=admin --admin_email=admin@admin.com

# Activate Tainacan
wp plugin activate $plugin_name


#npx cypress run --env host=$wp_url,baseUrl=$wp_url



# teardown cypress environment

# remove WordPress

# recover table prefix in wp_config
sed -i s/"\$table_prefix  = '$test_db_prefix';"/""/ $wp_base_dir/wp-config.php 

TEST_DB_NAME=`cat $wp_base_dir/wp-config.php | grep "define('DB_NAME'"| perl -pe 's|define\(.DB_NAME.,\s*.(.*?)\W\s*\).*|$1|'`
TEST_DB_USER=`cat $wp_base_dir/wp-config.php | grep "define('DB_USER'"| perl -pe 's|define\(.DB_USER.,\s*.(.*?)\W\s*\).*|$1|'`
TEST_DB_PASS=`cat $wp_base_dir/wp-config.php | grep "define('DB_PASSWORD'"| perl -pe 's|define\(.DB_PASSWORD.,\s*.(.*?)\W\s*\).*|$1|'`
TEST_DB_HOST=`cat $wp_base_dir/wp-config.php | grep "define('DB_HOST'"| perl -pe 's|define\(.DB_HOST.,\s*.(.*?)\W\s*\).*|$1|'`


echo DROP TABLE $test_db_prefix'comments' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'commentmeta' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'links' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'options' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'postmeta' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'posts' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'term_relationships' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'term_taxonomy' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'termmeta' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'terms' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'usermeta' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME
echo DROP TABLE $test_db_prefix'users' | mysql -u $TEST_DB_USER -p$TEST_DB_PASS -h $TEST_DB_HOST $TEST_DB_NAME

