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

sass -E 'UTF-8' --cache-location .tmp/sass-cache-4 src/views/media-component/media-component.scss:src/assets/css/media-component.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-5 src/views/gutenberg-blocks/tainacan-blocks/collections-list/style.scss:src/assets/css/tainacan-gutenberg-block-collections-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-6 src/views/gutenberg-blocks/tainacan-blocks/carousel-collections-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-collections-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-7 src/views/gutenberg-blocks/tainacan-blocks/items-list/style.scss:src/assets/css/tainacan-gutenberg-block-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-8 src/views/gutenberg-blocks/tainacan-blocks/dynamic-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-dynamic-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-9 src/views/gutenberg-blocks/tainacan-blocks/search-bar/style.scss:src/assets/css/tainacan-gutenberg-block-search-bar.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-10 src/views/gutenberg-blocks/tainacan-blocks/carousel-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-12 src/views/gutenberg-blocks/tainacan-blocks/carousel-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-12 src/views/gutenberg-blocks/tainacan-blocks/terms-list/style.scss:src/assets/css/tainacan-gutenberg-block-terms-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-13 src/views/gutenberg-blocks/tainacan-blocks/facets-list/style.scss:src/assets/css/tainacan-gutenberg-block-facets-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-14 src/views/gutenberg-blocks/tainacan-blocks/carousel-terms-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-terms-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-15 src/views/gutenberg-blocks/tainacan-blocks/faceted-search/style.scss:src/assets/css/tainacan-gutenberg-block-faceted-search.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-16 src/views/gutenberg-blocks/tainacan-blocks/item-submission-form/style.scss:src/assets/css/tainacan-gutenberg-block-item-submission-form.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-17 src/views/gutenberg-blocks/tainacan-blocks/carousel-related-items/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-related-items.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-18 src/views/gutenberg-blocks/gutenberg-blocks-style.scss:src/assets/css/tainacan-gutenberg-block-common-styles.css

echo "Compilação do Sass Concluído!"
exit 0
