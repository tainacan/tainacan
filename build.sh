#!/bin/bash

#source build-config.cfg
source build-config.cfg
#destination=~/devel/wordpress/wp-content/plugins/test-tainacan

sh compile-sass.sh
composer install

echo "Atualizando arquivos em $destination"
rm -r $destination
mkdir $destination
cp -R src/ $destination/
cp -R vendor/ $destination/
rm -rf $destination/scss
