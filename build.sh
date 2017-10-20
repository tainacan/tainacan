#!/usr/bin/env bash

#source build-config.sh
destination=~/devel/wordpress/wp-content/plugins/test-tainacan

sh compile-sass.sh

rm -r $destination
mkdir $destination
cp -R src/* $destination/
rm -rf $destination/scss
