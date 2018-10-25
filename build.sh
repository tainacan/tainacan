#!/bin/bash

source build-config.cfg

## Only run npm build if there was a change in a .js or .vue file
current_md5_js=$(<last-js-build.md5)
current_md5_sass=$(<last-sass-build.md5)
current_md5_composer=$(<last-composer-build.md5)
current_md5_package=$(<last-package-build.md5)

current_OS=`uname`

# For macOS (Darwin)
if [ $current_OS == "Darwin" ]; then
    find src ./webpack.config.js -type f \( -name "*.js" -or -name "*.vue" -or -name "webpack.config.js" \) -exec md5 {} \; | sort -k 2 | md5 > last-js-build.md5
    find ./src/scss/ ./src/admin/scss/ -type f \( -name "*.scss" \) -exec md5 {} \; | sort -k 2 | md5 > last-sass-build.md5
    find ./composer.json -type f \( -name "composer.json" \) -exec md5 {} \; | sort -k 2 | md5 > last-composer-build.md5
    find ./package.json -type f \( -name "package.json" -or -name "package-lock.json" \) -exec md5 {} \; | sort -k 2 | md5 > last-package-build.md5
else
    find src ./webpack.config.js -type f \( -name "*.js" -or -name "*.vue" -or -name "webpack.config.js" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-js-build.md5
    find ./src/scss/ ./src/admin/scss/ -type f \( -name "*.scss" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-sass-build.md5
    find ./composer.json -type f \( -name "composer.json" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-composer-build.md5
    find ./package.json -type f \( -name "package.json" -or -name "package-lock.json" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-package-build.md5
fi

new_md5_package=$(<last-package-build.md5)
if [ "$current_md5_package" != "$new_md5_package" ]
then
    ## Install js dependencies
    npm install
fi

new_md5_composer=$(<last-composer-build.md5)
if [ "$current_md5_composer" != "$new_md5_composer" ]
then
    ## Install composer dependencies
    composer install
fi

new_md5_sass=$(<last-sass-build.md5)
if [ "$current_md5_sass" != "$new_md5_sass" ]
then
    ## Compile SASS
    sh compile-sass.sh
fi

new_md5_js=$(<last-js-build.md5)
if [ "$current_md5_js" != "$new_md5_js" ]
then
    npm run build
fi
### END npm build ###

## Fetch PDF.js
if [ ! -d "src/pdf-viewer/pdfjs-dist" ]; then
  echo "Fething PDF.js"
  mkdir -p src/pdf-viewer/pdfjs-dist
  wget https://github.com/mozilla/pdf.js/releases/download/v1.9.426/pdfjs-1.9.426-dist.zip
  unzip pdfjs-1.9.426-dist.zip -d src/pdf-viewer/pdfjs-dist/
  rm pdfjs-1.9.426-dist.zip
fi


echo "Updating files in $wp_plugin_dir"

rm -rf $wp_plugin_dir

mkdir $wp_plugin_dir

rsync -axz --exclude='vendor/bin/phpc*' --exclude='vendor/squizlabs' --exclude='vendor/wimg' \
 --exclude='vendor/respect/validation/.git' --exclude='vendor/symfony/polyfill-mbstring/.git' --exclude='pdf-viewer/pdfjs-dist/web/compressed.tracemonkey-pldi-09.pdf' \
  src/* $wp_plugin_dir/

rm -rf $wp_plugin_dir/scss

echo "Build complete!"
