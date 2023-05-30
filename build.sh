#!/bin/bash

# Loads user-defined variables at build-config file
source build-config.cfg

## Only run npm build if there was a change in a .js or .vue file
current_md5_js=$(<last-js-build.md5)
current_md5_sass=$(<last-sass-build.md5)
current_md5_composer=$(<last-composer-build.md5)
current_md5_package=$(<last-package-build.md5)

current_OS=`uname`

# For macOS (Darwin)
if [ $current_OS == "Darwin" ]; then
    find src *.js -type f \( -name "*.js" -or -name "*.vue" -or -name "webpack.common.js" -or -name "webpack.dev.js" -or -name "webpack.prod.js" \) -exec md5 {} \; | sort -k 2 | md5 > last-js-build.md5
    find ./src/views/admin/scss/ ./src/views/gutenberg-blocks/ ./src/views/gutenberg-blocks/blocks/collections-list ./src/views/gutenberg-blocks/blocks/facets-list ./src/views/gutenberg-blocks/blocks/dynamic-items-list ./src/views/gutenberg-blocks/blocks/items-list ./src/views/gutenberg-blocks/blocks/terms-list -type f \( -name "*.scss" \) -exec md5 {} \; | sort -k 2 | md5 > last-sass-build.md5
    find ./composer.json -type f \( -name "composer.json" \) -exec md5 {} \; | sort -k 2 | md5 > last-composer-build.md5
    find ./package.json -type f \( -name "package.json" -or -name "package-lock.json" \) -exec md5 {} \; | sort -k 2 | md5 > last-package-build.md5
else
    find src *.js -type f \( -name "*.js" -or -name "*.vue" -or -name "webpack.common.js" -or -name "webpack.dev.js" -or -name "webpack.prod.js" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-js-build.md5
    find ./src/views/admin/scss/ ./src/views/gutenberg-blocks ./src/views/gutenberg-blocks/blocks/collections-list ./src/views/gutenberg-blocks/blocks/facets-list ./src/views/gutenberg-blocks/blocks/dynamic-items-list ./src/views/gutenberg-blocks/blocks/items-list ./src/views/gutenberg-blocks/blocks/terms-list -type f \( -name "*.scss" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-sass-build.md5
    find ./composer.json -type f \( -name "composer.json" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-composer-build.md5
    find ./package.json -type f \( -name "package.json" -or -name "package-lock.json" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-package-build.md5
fi

new_md5_package=$(<last-package-build.md5)
if [ "$current_md5_package" != "$new_md5_package" ]
then
    ## Install js dependencies
    npm ci
fi

is_prod_build=false
for i in "$@"
do
    case $i in
        --prod)
            is_prod_build=true
            rm -r ./src/assets/js/*
        ;;
    esac
done

new_md5_composer=$(<last-composer-build.md5)
if [ "$current_md5_composer" != "$new_md5_composer" ]
then
    ## Install composer dependencies
    if [ "$is_prod_build" == false ]
    then
        composer install
    else
        composer install --no-dev
    fi
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
    if [ "$is_prod_build" == false ]
    then
        echo "$(tput setab 2)  $(tput sgr 0) $(tput setab 2) $(tput sgr 0) Building in development mode $(tput setab 2) $(tput sgr 0) $(tput setab 2)  $(tput sgr 0)"
        npm run build
    else
        echo "$(tput setab 4)  $(tput sgr 0) $(tput setab 4) $(tput sgr 0) Building in production mode $(tput setab 4) $(tput sgr 0) $(tput setab 4)  $(tput sgr 0)"
        npm run build-prod
    fi
fi

## Fetch PDF.js
## Commented as we have a modified version of its code.
# if [ ! -d "src/pdf-viewer/pdfjs-dist" ]; then
#   echo "Fething PDF.js"
#   mkdir -p src/pdf-viewer/pdfjs-dist
#   wget https://github.com/mozilla/pdf.js/releases/download/v1.9.426/pdfjs-1.9.426-dist.zip
#   unzip pdfjs-1.9.426-dist.zip -d src/pdf-viewer/pdfjs-dist/
#   rm pdfjs-1.9.426-dist.zip
# fi

echo "Updating files in $wp_plugin_dir"

rm -rf $wp_plugin_dir

mkdir $wp_plugin_dir

rsync -axz --exclude='vendor/bin/phpc*' --exclude='vendor/squizlabs' --exclude='vendor/wimg' \
 --exclude='vendor/respect/validation/.git' --exclude='vendor/symfony/polyfill-mbstring/.git' \
 --exclude='vendor/respect/validation/docs' --exclude='vendor/respect/validation/tests' \
 --exclude='views/libs/pdf-viewer/pdfjs-dist/web/compressed.tracemonkey-pldi-09.pdf' \
 --exclude='vendor/tecnickcom/tcpdf/fonts' \
 --exclude='vendor/smalot/pdfparser/src/Smalot/PdfParser/Tests/' \
 --exclude='vendor/tecnickcom/tcpdf/examples' \
  src/* $wp_plugin_dir/

echo "Removing unecessary source files"
rm -rf $wp_plugin_dir/scss
find $wp_plugin_dir/views/ -type f -name '*.vue' -exec rm {} +
find $wp_plugin_dir/views/ -type f -name '*.scss' -exec rm {} +
find $wp_plugin_dir/views/ -type f -name '*.sass' -exec rm {} +
find $wp_plugin_dir/views/ -type f -name '*.js' ! -path "${wp_plugin_dir}/views/libs/*" -exec rm {} +
find $wp_plugin_dir/views/ -type d -empty -delete

if [ "$is_prod_build" == true ]
then
    find $wp_plugin_dir/assets/js/ -type f -name '*.js.map' -exec rm {} +
fi

echo "Build complete!"
