#!/bin/bash
 
# Executa o comando 'sass' para verificar se existe (veja http://stackoverflow.com/a/677212/329911)
command -v sass >/dev/null 2>&1 || {
  echo >&2 "Sass parece não estar disponivel.";
  exit 1;
}

echo "Compilando Sass..."

sass -E 'UTF-8' --cache-location .tmp/sass-cache-1 src/views/admin/scss/tainacan-admin.scss:src/assets/css/tainacan-admin.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-2 src/views/roles/tainacan-roles.scss:src/assets/css/tainacan-roles.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-3 src/views/reports/tainacan-reports.scss:src/assets/css/tainacan-reports.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-4 src/views/mobile-app/tainacan-mobile-app.scss:src/assets/css/tainacan-mobile-app.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-5 src/views/gutenberg-blocks/blocks/item-gallery/style.scss:src/assets/css/tainacan-gutenberg-block-item-gallery.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-6 src/views/gutenberg-blocks/blocks/collections-list/style.scss:src/assets/css/tainacan-gutenberg-block-collections-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-7 src/views/gutenberg-blocks/blocks/carousel-collections-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-collections-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-8 src/views/gutenberg-blocks/blocks/items-list/style.scss:src/assets/css/tainacan-gutenberg-block-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-9 src/views/gutenberg-blocks/blocks/dynamic-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-dynamic-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-10 src/views/gutenberg-blocks/blocks/search-bar/style.scss:src/assets/css/tainacan-gutenberg-block-search-bar.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-11 src/views/gutenberg-blocks/blocks/carousel-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-13 src/views/gutenberg-blocks/blocks/carousel-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-14 src/views/gutenberg-blocks/blocks/terms-list/style.scss:src/assets/css/tainacan-gutenberg-block-terms-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-15 src/views/gutenberg-blocks/blocks/facets-list/style.scss:src/assets/css/tainacan-gutenberg-block-facets-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-16 src/views/gutenberg-blocks/blocks/carousel-terms-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-terms-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-17 src/views/gutenberg-blocks/blocks/faceted-search/style.scss:src/assets/css/tainacan-gutenberg-block-faceted-search.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-18 src/views/gutenberg-blocks/blocks/item-submission-form/style.scss:src/assets/css/tainacan-gutenberg-block-item-submission-form.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-19 src/views/gutenberg-blocks/blocks/related-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-related-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-20 src/views/gutenberg-blocks/blocks/item-metadata/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadata.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-21 src/views/gutenberg-blocks/blocks/item-metadata-section/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadata-section.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-22 src/views/gutenberg-blocks/blocks/item-metadata-sections/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadata-sections.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-23 src/views/gutenberg-blocks/blocks/item-metadatum/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadatum.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-24 src/views/gutenberg-blocks/blocks/geocoordinate-item-metadatum/style.scss:src/assets/css/tainacan-gutenberg-block-geocoordinate-item-metadatum.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-25 src/views/gutenberg-blocks/blocks/metadata-section-name/style.scss:src/assets/css/tainacan-gutenberg-block-metadata-section-name.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-26 src/views/gutenberg-blocks/blocks/metadata-section-description/style.scss:src/assets/css/tainacan-gutenberg-block-metadata-section-description.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-27 src/views/gutenberg-blocks/scss/gutenberg-blocks-editor-style.scss:src/assets/css/tainacan-gutenberg-block-common-editor-styles.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-28 src/views/gutenberg-blocks/scss/gutenberg-blocks-theme-style.scss:src/assets/css/tainacan-gutenberg-block-common-theme-styles.css

echo "Compilação do Sass Concluído!"
exit 0
