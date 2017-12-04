#!/bin/bash

source build-config.cfg

## Only run npm build if there was a change in a .js or .vue file
current_md5=$(<last-js-build.md5)
find src -type f \( -name "*.js" -or -name "*.vue" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-js-build.md5
new_md5=$(<last-js-build.md5)
if [ "$current_md5" != "$new_md5" ]
then
    npm run build
fi
### END npm build ###

## Compile SASS
sh compile-sass.sh

## Install composer dependencies
composer install

echo "Updating files in $destination"
rm -rf $destination
mkdir $destination
cp -R src/* $destination/
rm -rf $destination/scss
