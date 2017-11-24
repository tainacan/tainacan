#!/bin/bash

#source build-config.cfg
source build-config.cfg
#destination=~/devel/wordpress/wp-content/plugins/tainacan

sh compile-sass.sh
composer install

echo "Atualizando arquivos em $destination"
rm -rf $destination
mkdir $destination
cp -R src/ $destination/
rm -rf $destination/scss
