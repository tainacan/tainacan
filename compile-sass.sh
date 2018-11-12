#!/bin/bash
 
# Executa o comando 'sass' para verificar se existe (veja http://stackoverflow.com/a/677212/329911)
command -v sass >/dev/null 2>&1 || {
  echo >&2 "SASS parece não está disponivel.";
  exit 1;
}
 
# Define o caminho.
echo "Compilando Sass..."

sass -E 'UTF-8' --cache-location .tmp/sass-cache-1 src/scss/tainacan-embeds.scss:src/assets/css/tainacan-embeds.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-2 src/admin/scss/tainacan-admin.scss:src/assets/css/tainacan-admin.css

sass -E 'UTF-8' --cache-location .tmp/sass-cache-3 src/gutenberg-blocks/gutenberg-blocks-style.scss:src/assets/css/tainacan-gutenberg-blocks-style.css

echo "Compilação do Sass Concluído!"
exit 0
