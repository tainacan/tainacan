#!/bin/bash

#source build-config.cfg
source build-config.cfg
#destination=~/devel/wordpress/wp-content/plugins/test-tainacan

sh compile-sass.sh

echo "Atualizando arquivos em $destination"
rm -r $destination
mkdir $destination
cp -R src/* $destination/
rm -rf $destination/scss
