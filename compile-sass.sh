#!/bin/bash
 
# Executa o comando 'sass' para verificar se existe (veja http://stackoverflow.com/a/677212/329911)
command -v sass >/dev/null 2>&1 || {
  echo >&2 "Sass parece não estar disponivel.";
  exit 1;
}

echo "Compilando Sass..."

sass src/views/admin/scss/tainacan-admin.scss:src/assets/css/tainacan-admin.css

sass src/views/roles/tainacan-roles.scss:src/assets/css/tainacan-roles.css

sass src/views/mobile-app/tainacan-mobile-app.scss:src/assets/css/tainacan-mobile-app.css

sass src/views/gutenberg-blocks/blocks/item-gallery/style.scss:src/assets/css/tainacan-gutenberg-block-item-gallery.css

sass src/views/gutenberg-blocks/blocks/collections-list/style.scss:src/assets/css/tainacan-gutenberg-block-collections-list.css

sass src/views/gutenberg-blocks/blocks/carousel-collections-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-collections-list.css

sass src/views/gutenberg-blocks/blocks/items-list/style.scss:src/assets/css/tainacan-gutenberg-block-items-list.css

sass src/views/gutenberg-blocks/blocks/dynamic-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-dynamic-items-list.css

sass src/views/gutenberg-blocks/blocks/search-bar/style.scss:src/assets/css/tainacan-gutenberg-block-search-bar.css

sass src/views/gutenberg-blocks/blocks/carousel-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-items-list.css

sass src/views/gutenberg-blocks/blocks/carousel-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-items-list.css

sass src/views/gutenberg-blocks/blocks/terms-list/style.scss:src/assets/css/tainacan-gutenberg-block-terms-list.css

sass src/views/gutenberg-blocks/blocks/facets-list/style.scss:src/assets/css/tainacan-gutenberg-block-facets-list.css

sass src/views/gutenberg-blocks/blocks/carousel-terms-list/style.scss:src/assets/css/tainacan-gutenberg-block-carousel-terms-list.css

sass src/views/gutenberg-blocks/blocks/faceted-search/style.scss:src/assets/css/tainacan-gutenberg-block-faceted-search.css

sass src/views/gutenberg-blocks/blocks/item-submission-form/style.scss:src/assets/css/tainacan-gutenberg-block-item-submission-form.css

sass src/views/gutenberg-blocks/blocks/related-items-list/style.scss:src/assets/css/tainacan-gutenberg-block-related-items-list.css

sass src/views/gutenberg-blocks/blocks/item-metadata/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadata.css

sass src/views/gutenberg-blocks/blocks/item-metadata-section/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadata-section.css

sass src/views/gutenberg-blocks/blocks/item-metadata-sections/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadata-sections.css

sass src/views/gutenberg-blocks/blocks/item-metadatum/style.scss:src/assets/css/tainacan-gutenberg-block-item-metadatum.css

sass src/views/gutenberg-blocks/blocks/geocoordinate-item-metadatum/style.scss:src/assets/css/tainacan-gutenberg-block-geocoordinate-item-metadatum.css

sass src/views/gutenberg-blocks/blocks/metadata-section-name/style.scss:src/assets/css/tainacan-gutenberg-block-metadata-section-name.css

sass src/views/gutenberg-blocks/blocks/metadata-section-description/style.scss:src/assets/css/tainacan-gutenberg-block-metadata-section-description.css

sass src/views/gutenberg-blocks/scss/gutenberg-blocks-editor-style.scss:src/assets/css/tainacan-gutenberg-block-common-editor-styles.css

sass src/views/gutenberg-blocks/scss/gutenberg-blocks-theme-style.scss:src/assets/css/tainacan-gutenberg-block-common-theme-styles.css

sass src/views/tainacan-pages.scss:src/assets/css/tainacan-pages.css

sass src/views/dashboard/tainacan-dashboard.scss:src/assets/css/tainacan-dashboard.css

sass src/views/settings/tainacan-settings.scss:src/assets/css/tainacan-settings.css

echo "Compilação do Sass Concluído!"
exit 0
