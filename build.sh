#!/bin/bash

# Loads user-defined variables at build-config file
if [ -f build-config.cfg ]; then
    source build-config.cfg
elif [ -f build-config-sample.cfg ]; then
    echo "Warning: build-config.cfg not found. Using build-config-sample.cfg defaults..."
    source build-config-sample.cfg
fi

## Only run npm build if there was a change in a .js or .vue file
current_md5_js=$([ -f last-js-build.md5 ] && cat last-js-build.md5 2>/dev/null || echo "")
current_md5_sass=$([ -f last-sass-build.md5 ] && cat last-sass-build.md5 2>/dev/null || echo "")
current_md5_composer=$([ -f last-composer-build.md5 ] && cat last-composer-build.md5 2>/dev/null || echo "")
current_md5_package=$([ -f last-package-build.md5 ] && cat last-package-build.md5 2>/dev/null || echo "")

current_OS=`uname`

# For macOS (Darwin)
if [ $current_OS == "Darwin" ]; then
    find src *.js -type f \( -name "*.js" -or -name "*.vue" -or -name "webpack.common.js" -or -name "webpack.dev.js" -or -name "webpack.prod.js" \) -exec md5 {} \; | sort -k 2 | md5 > last-js-build.md5
    find src -type f -name "*.scss" -exec md5 {} \; | sort -k 2 | md5 > last-sass-build.md5
    find ./composer.json -type f \( -name "composer.json" \) -exec md5 {} \; | sort -k 2 | md5 > last-composer-build.md5
    find ./package.json -type f \( -name "package.json" -or -name "package-lock.json" \) -exec md5 {} \; | sort -k 2 | md5 > last-package-build.md5
else
    find src *.js -type f \( -name "*.js" -or -name "*.vue" -or -name "webpack.common.js" -or -name "webpack.dev.js" -or -name "webpack.prod.js" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-js-build.md5
    find src -type f -name "*.scss" -exec md5sum {} \; | sort -k 2 | md5sum > last-sass-build.md5
    find ./composer.json -type f \( -name "composer.json" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-composer-build.md5
    find ./package.json -type f \( -name "package.json" -or -name "package-lock.json" \) -exec md5sum {} \; | sort -k 2 | md5sum > last-package-build.md5
fi

new_md5_package=$(<last-package-build.md5)
if [ "$current_md5_package" != "$new_md5_package" ] && [ ! -d "node_modules" ]
then
    ## Install js dependencies
    npm ci || npm install --prefer-offline || true
fi

is_prod_build=false
for i in "$@"
do
    case $i in
        --prod)
            is_prod_build=true
        ;;
    esac
done

new_md5_composer=$(<last-composer-build.md5)
if [ "$current_md5_composer" != "$new_md5_composer" ]
then
    ## Install composer dependencies
    if command -v composer >/dev/null 2>&1; then
        if [ "$is_prod_build" == false ]
        then
            composer install
        else
            composer install --no-dev
        fi
    else
        echo "Notice: composer command not found. Skipping composer install."
    fi
fi

new_md5_sass=$(<last-sass-build.md5)
if [ "$current_md5_sass" != "$new_md5_sass" ]
then
    ## Compile SASS
    if [ "$is_prod_build" == true ]
    then
        sh compile-sass.sh --prod
    else
        sh compile-sass.sh
    fi
fi

new_md5_js=$(<last-js-build.md5)

if [ "$current_md5_js" != "$new_md5_js" ] || [ ! -d "src/assets/js" ] || [ -z "$(ls -A src/assets/js 2>/dev/null)" ]
then
    if [ "$is_prod_build" == false ]
    then
        echo "$(tput setab 2)  $(tput sgr 0) $(tput setab 2) $(tput sgr 0) Building in development mode $(tput setab 2) $(tput sgr 0) $(tput setab 2)  $(tput sgr 0)"
        npm run build
    else
        echo "$(tput setab 4)  $(tput sgr 0) $(tput setab 4) $(tput sgr 0) Building in production mode $(tput setab 4) $(tput sgr 0) $(tput setab 4)  $(tput sgr 0)"
        rm -r ./src/assets/js/*
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

if [ -n "$wp_plugin_dir" ] && [ -d "$(dirname "$wp_plugin_dir")" ]; then
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
     --exclude='vendor/composer/installed.json' \
      src/* $wp_plugin_dir/

    echo "Removing unecessary source files"
    rm -rf $wp_plugin_dir/scss
    find $wp_plugin_dir/views/ -type f -name '*.vue' -exec rm {} +
    find $wp_plugin_dir/views/ -type f -name '*.scss' -exec rm {} +
    find $wp_plugin_dir/views/ -type f -name '*.sass' -exec rm {} +
    find $wp_plugin_dir/views/ -type f -name '*.js' -exec rm {} +
    find $wp_plugin_dir/views/ -type d -empty -delete

    if [ "$is_prod_build" == true ]
    then
        echo "Removing legacy source code that is not used in production..."
        find $wp_plugin_dir/classes/class-tainacan-bulk-edit.php -type f -delete
        find $wp_plugin_dir/classes/exposers/class-tainacan-oai-pmh.php -type f -delete
        find $wp_plugin_dir/classes/exposers/class-tainacan-json-ld.php -type f -delete
        find $wp_plugin_dir/classes/exposers/class-tainacan-txt.php -type f -delete
        find $wp_plugin_dir/classes/exposers/class-tainacan-xml.php -type f -delete
    fi

    if [ "$is_prod_build" == true ]
    then
        find $wp_plugin_dir/assets/js/ -type f -name '*.js.map' -exec rm {} +
    fi
else
    echo "Skipping rsync to WordPress plugins directory (directory not set or parent directory does not exist)."
fi

echo "Build complete!"

