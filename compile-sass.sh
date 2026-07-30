#!/bin/bash

# Resolves the local 'sass' binary
SASS_BIN=""
if [ -x "./node_modules/.bin/sass" ]; then
  SASS_BIN="./node_modules/.bin/sass"
elif npx --no-install sass --version >/dev/null 2>&1; then
  SASS_BIN="npx --no-install sass"
else
  echo >&2 "Sass is not installed in this project. Run 'npm install'."
  exit 1
fi

# Check if production mode is enabled (via --prod flag or environment variable)
SASS_STYLE="expanded"
if [ "$1" = "--prod" ] || [ "$PROD_BUILD" = "true" ]; then
  SASS_STYLE="compressed"
  echo "Compiling Sass in production mode (minified)..."
else
  echo "Compiling Sass..."
fi

$SASS_BIN --style=$SASS_STYLE --load-path=node_modules --silence-deprecation=global-builtin \
  src/views/admin/scss/tainacan-admin.scss:src/assets/css/tainacan-admin.css \
  src/views/roles/tainacan-roles.scss:src/assets/css/tainacan-roles.css \
  src/views/mobile-app/tainacan-mobile-app.scss:src/assets/css/tainacan-mobile-app.css \
  src/views/gutenberg-blocks/blocks/item-gallery/style.scss:src/assets/css/tainacan-gutenberg-block-item-gallery.css \
  src/views/gutenberg-blocks/blocks/collections-list/style.scss:src/assets/css/tainacan-gutenberg-block-collections-list.css \
  src/views/gutenberg-blocks/blocks/carousel-collections-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-collections-list.css \
  src/views/gutenberg-blocks/blocks/dynamic-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-dynamic-items-list.css \
  src/views/gutenberg-blocks/blocks/search-bar/style.scss:src/assets/css/tainacan-gutenberg-block-search-bar.css \
  src/views/gutenberg-blocks/blocks/carousel-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-items-list.css \
  src/views/gutenberg-blocks/blocks/terms-list/style.scss:src/assets/css/tainacan-gutenberg-block-terms-list.css \
  src/views/gutenberg-blocks/blocks/facets-list/style.scss:src/assets/css/tainacan-gutenberg-block-facets-list.css \
  src/views/gutenberg-blocks/blocks/carousel-terms-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-terms-list.css \
  src/views/gutenberg-blocks/blocks/faceted-search/style.scss:src/assets/css/tainacan-gutenberg-block-faceted-search.css \
  src/views/gutenberg-blocks/blocks/item-submission-form/style.scss:src/assets/css/tainacan-gutenberg-block-item-submission-form.css \
  src/views/gutenberg-blocks/blocks/related-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-related-items-list.css \
  src/views/gutenberg-blocks/blocks/item-metadata/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadata.css \
  src/views/gutenberg-blocks/blocks/item-metadata-section/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadata-section.css \
  src/views/gutenberg-blocks/blocks/item-metadata-sections/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadata-sections.css \
  src/views/gutenberg-blocks/blocks/item-metadatum/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadatum.css \
  src/views/gutenberg-blocks/blocks/geocoordinate-item-metadatum/style.scss:src/assets/css/tainacan-gutenberg-block-geocoordinate-item-metadatum.css \
  src/views/gutenberg-blocks/blocks/metadata-section-name/style.scss:src/assets/css/tainacan-gutenberg-block-metadata-section-name.css \
  src/views/gutenberg-blocks/blocks/metadata-section-description/style.scss:src/assets/css/tainacan-gutenberg-block-metadata-section-description.css \
  src/views/gutenberg-blocks/scss/gutenberg-blocks-editor-style.scss:src/assets/css/tainacan-gutenberg-block-common-editor-styles.css \
  src/views/gutenberg-blocks/scss/gutenberg-blocks-theme-style.scss:src/assets/css/tainacan-gutenberg-block-common-theme-styles.css \
  src/views/tainacan-pages.scss:src/assets/css/tainacan-pages.css \
  src/views/dashboard/tainacan-dashboard.scss:src/assets/css/tainacan-dashboard.css \
  src/views/settings/tainacan-settings.scss:src/assets/css/tainacan-settings.css

echo "Sass Compilation Finished!"
exit 0
