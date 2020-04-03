#!/bin/bash
 
# Executa o comando 'sass' para verificar se existe (veja http://stackoverflow.com/a/677212/329911)
command -v sass >/dev/null 2>&1 || {
  echo >&2 "SASS parece não está disponivel.";
  exit 1;
}
 
# Define o caminho.
echo "Compilando Sass..."

sass -E 'UTF-8' --cache-location .tmp/sass-cache-1 src/views/admin/scss/tainacan-admin.scss:src/assets/css/tainacan-admin.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-2 src/views/roles/tainacan-roles.scss:src/assets/css/tainacan-roles.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-3 src/views/gutenberg-blocks/tainacan-collections/collections-list/collections-list.scss:src/assets/css/tainacan-gutenberg-block-collections-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-4 src/views/gutenberg-blocks/tainacan-collections/carousel-collections-list/carousel-collections-list.scss:src/assets/css/tainacan-gutenberg-block-carousel-collections-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-5 src/views/gutenberg-blocks/tainacan-items/items-list/items-list.scss:src/assets/css/tainacan-gutenberg-block-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-6 src/views/gutenberg-blocks/tainacan-items/dynamic-items-list/dynamic-items-list.scss:src/assets/css/tainacan-gutenberg-block-dynamic-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-7 src/views/gutenberg-blocks/tainacan-items/search-bar/search-bar.scss:src/assets/css/tainacan-gutenberg-block-search-bar.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-8 src/views/gutenberg-blocks/tainacan-items/carousel-items-list/carousel-items-list.scss:src/assets/css/tainacan-gutenberg-block-carousel-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-9 src/views/gutenberg-blocks/tainacan-items/carousel-items-list/carousel-items-list.scss:src/assets/css/tainacan-gutenberg-block-carousel-items-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-10 src/views/gutenberg-blocks/tainacan-terms/terms-list/terms-list.scss:src/assets/css/tainacan-gutenberg-block-terms-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-11 src/views/gutenberg-blocks/tainacan-facets/facets-list/facets-list.scss:src/assets/css/tainacan-gutenberg-block-facets-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-12 src/views/gutenberg-blocks/tainacan-terms/carousel-terms-list/carousel-terms-list.scss:src/assets/css/tainacan-gutenberg-block-carousel-terms-list.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-13 src/views/gutenberg-blocks/tainacan-facets/faceted-search/faceted-search.scss:src/assets/css/tainacan-gutenberg-block-faceted-search.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-14 src/views/gutenberg-blocks/gutenberg-blocks-style.scss:src/assets/css/tainacan-gutenberg-block-common-styles.css

echo "Compilação do Sass Concluído!"
exit 0
