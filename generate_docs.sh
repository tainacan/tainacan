#!/bin/bash
set -euo pipefail

# Generate developer documentation into docs/ (phpdoc, hooks, openapi, diagrams).
# Run from the plugin repository root or call this script directly.
#
# Example (Docker):
#   docker exec -it tainacan_fpm_apache bash
#   cd /src/tainacan && ./generate_docs.sh

PLUGIN_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPTS_DIR="$PLUGIN_ROOT/docs/scripts"
DOCS_DIR="$PLUGIN_ROOT/docs"
SRC_DIR="$PLUGIN_ROOT/src"
VENDOR_BIN="$PLUGIN_ROOT/src/vendor/bin"
PHPDOC_THEME="$PLUGIN_ROOT/src/vendor/saggre/phpdocumentor-markdown/themes/markdown"
PHPDOC_BIN="$SCRIPTS_DIR/.tools/phpDocumentor.phar"
WP_PATH="${WP_PATH:-/var/www/html/public}"

if [ ! -f "$SRC_DIR/tainacan.php" ]; then
    echo "Error: Could not find the Tainacan plugin at $PLUGIN_ROOT"
    echo "Run this script from the plugin repository (tainacan/)."
    exit 1
fi

ensure_doc_dependencies() {
    local missing=0

    if [ ! -x "$VENDOR_BIN/wp-documentor" ] || \
       [ ! -x "$VENDOR_BIN/php-class-diagram" ] || \
       [ ! -d "$PHPDOC_THEME" ]; then
        missing=1
    fi

    if [ "$missing" -eq 1 ]; then
        echo "Installing documentation dependencies (composer install)..."
        if ! command -v composer >/dev/null 2>&1; then
            echo "Error: composer is not available. Install dev dependencies from the plugin root:"
            echo "  cd $PLUGIN_ROOT && composer install"
            exit 1
        fi
        (cd "$PLUGIN_ROOT" && composer install --no-interaction)
    fi

    if [ ! -f "$PHPDOC_BIN" ]; then
        echo "Downloading phpDocumentor..."
        mkdir -p "$(dirname "$PHPDOC_BIN")"
        wget -q -O "$PHPDOC_BIN" https://phpdoc.org/phpDocumentor.phar
    fi

    if [ ! -x "$VENDOR_BIN/wp-documentor" ] || [ ! -d "$PHPDOC_THEME" ]; then
        echo "Error: documentation dependencies are missing. Run:"
        echo "  cd $PLUGIN_ROOT && composer install"
        exit 1
    fi
}

set_phpdoc_title() {
    local file="$1"
    local title="$2"

    while [ -f "$file" ]; do
        case "$(head -n 1 "$file")" in
            "# $title"|"")
                sed -i '1d' "$file"
                ;;
            *)
                break
                ;;
        esac
    done

    sed -i "1i# $title\n" "$file"
}

ensure_doc_dependencies

echo "Generating phpDocumentor documentation..."
php "$PHPDOC_BIN" \
    -d "$SRC_DIR" \
    -t "$DOCS_DIR/phpdoc" \
    --cache-folder="$SCRIPTS_DIR/.tools/phpdoc-cache" \
    --template="$PHPDOC_THEME" \
    --ignore="vendor/" \
    --ignore="node_modules/" \
    --ignore="tests/" \
    --ignore="*.min.js" \
    --ignore="*.min.css"

echo "Adding titles to documentation files..."
while IFS= read -r -d '' file; do
    filename=$(basename "$file" .md)
    classname=$(echo "$filename" | sed "s/.*\\\\.//")
    set_phpdoc_title "$file" "$classname"
done < <(find "$DOCS_DIR/phpdoc/classes" -name "*.md" -type f -print0)

while IFS= read -r -d '' file; do
    filename=$(basename "$file" .md)
    set_phpdoc_title "$file" "$filename"
done < <(find "$DOCS_DIR/phpdoc/functions" -name "*.md" -type f -print0)

echo "Fixing documentation links for GitHub Pages..."
find "$DOCS_DIR/phpdoc" -name "*.md" -type f -exec sed -i 's|\./functions/|/dev/phpdoc/functions/|g' {} \;
find "$DOCS_DIR/phpdoc" -name "*.md" -type f -exec sed -i 's|\./classes/|/dev/phpdoc/classes/|g' {} \;
find "$DOCS_DIR/phpdoc" -name "*.md" -type f -exec sed -i 's|\./namespaces/|/dev/phpdoc/namespaces/|g' {} \;
find "$DOCS_DIR/phpdoc" -name "*.md" -type f -exec sed -i 's|\./packages/|/dev/phpdoc/packages/|g' {} \;

find "$DOCS_DIR/phpdoc" -name "*.md" -type f -exec sed -i 's|/dev/phpdoc/functions/\([^)]*\))|/dev/phpdoc/functions/\1.md)|g' {} \;
find "$DOCS_DIR/phpdoc" -name "*.md" -type f -exec sed -i 's|/dev/phpdoc/classes/\([^)]*\))|/dev/phpdoc/classes/\1.md)|g' {} \;
find "$DOCS_DIR/phpdoc" -name "*.md" -type f -exec sed -i 's|/dev/phpdoc/namespaces/\([^)]*\))|/dev/phpdoc/namespaces/\1.md)|g' {} \;
find "$DOCS_DIR/phpdoc" -name "*.md" -type f -exec sed -i 's|/dev/phpdoc/packages/\([^)]*\))|/dev/phpdoc/packages/\1.md)|g' {} \;

echo "Generating WordPress hooks documentation..."

if [ ! -f "$SCRIPTS_DIR/templates/actions.php" ]; then
    echo "Warning: Actions template not found at $SCRIPTS_DIR/templates/actions.php"
else
    "$VENDOR_BIN/wp-documentor" parse "$PLUGIN_ROOT" \
        --template="$SCRIPTS_DIR/templates/actions.php" \
        --exclude="vendor" --exclude="tests" --exclude="docs" \
        --output="$DOCS_DIR/actions.md" --format=markdown
fi

if [ ! -f "$SCRIPTS_DIR/templates/filters.php" ]; then
    echo "Warning: Filters template not found at $SCRIPTS_DIR/templates/filters.php"
else
    "$VENDOR_BIN/wp-documentor" parse "$PLUGIN_ROOT" \
        --template="$SCRIPTS_DIR/templates/filters.php" \
        --exclude="vendor" --exclude="tests" --exclude="docs" \
        --output="$DOCS_DIR/filters.md" --format=markdown
fi

echo "Checking for OpenAPI generator plugin..."
if command -v wp >/dev/null 2>&1 && wp --path="$WP_PATH" plugin is-active document-generator-for-openapi --allow-root 2>/dev/null; then
    echo -e "\e[34m ### Generating OpenAPI REST file ### \e[0m"
    if wp --path="$WP_PATH" openapi-generator export-file tainacan/v2 \
        --destination="$DOCS_DIR/openapi.json" --allow-root; then
        echo -e "\e[32m ### OpenAPI documentation generated successfully ### \e[0m"
    else
        echo -e "\e[31m ### Error generating OpenAPI documentation ### \e[0m"
    fi
else
    echo -e "\e[33m ### Skipping OpenAPI export (wp-cli or document-generator-for-openapi not available) ### \e[0m"
fi

echo -e "\e[34m ### Generating Mermaid class diagrams ### \e[0m"
mkdir -p "$DOCS_DIR/diagrams"
mkdir -p "$DOCS_DIR/diagrams/namespaces"

if [ -x "$VENDOR_BIN/php-class-diagram" ]; then
    echo "Generating class diagrams from phpDocumentor output..."

    echo "  Step 1: Generating main overview PlantUML..."
    "$VENDOR_BIN/php-class-diagram" "$SRC_DIR/classes" \
        > "$DOCS_DIR/diagrams/tainacan-overview.puml" 2>/dev/null || true

    echo "  Step 2: Extracting class information from phpDocumentor output..."
    if [ -f "$SCRIPTS_DIR/generate-diagrams-from-phpdoc.php" ] && \
       [ -d "$DOCS_DIR/phpdoc/classes" ]; then
        echo "    Running PHP script to extract class information..."
        php "$SCRIPTS_DIR/generate-diagrams-from-phpdoc.php" \
            "$DOCS_DIR/phpdoc/classes" \
            > /tmp/tainacan-classes-from-phpdoc.json 2> /tmp/phpdoc-extract-errors.log
        php_exit_code=$?

        if [ -s "/tmp/phpdoc-extract-errors.log" ]; then
            echo "      Warnings/Errors from PHP script:"
            sed 's/^/        /' /tmp/phpdoc-extract-errors.log
        fi

        if [ $php_exit_code -ne 0 ]; then
            echo "      ERROR: PHP script failed with exit code $php_exit_code"
            exit 1
        fi

        if [ ! -f "/tmp/tainacan-classes-from-phpdoc.json" ] || [ ! -s "/tmp/tainacan-classes-from-phpdoc.json" ]; then
            echo "      ERROR: JSON file was not created or is empty"
            exit 1
        fi

        json_size=$(wc -c < /tmp/tainacan-classes-from-phpdoc.json)
        echo "    Extracted class information ($json_size bytes)"

        echo "  Step 3: Generating namespace overview PlantUML files..."
        php -r "
            \$data = json_decode(file_get_contents('/tmp/tainacan-classes-from-phpdoc.json'), true);
            \$srcClasses = '$SRC_DIR/classes';
            if (\$data && isset(\$data['namespaces'])) {
                foreach (\$data['namespaces'] as \$ns => \$classes) {
                    if (!empty(\$ns)) {
                        \$nsPath = str_replace('\\\\', '/', \$ns);
                        \$nsParts = explode('/', \$nsPath);

                        if (count(\$nsParts) > 1 && \$nsParts[0] === 'Tainacan') {
                            \$subNs = \$nsParts[1];
                            \$dir = \$srcClasses . '/';

                            if (\$subNs === 'API' && isset(\$nsParts[2]) && \$nsParts[2] === 'EndPoints') {
                                \$dir .= 'api/endpoints';
                            } elseif (\$subNs === 'Repositories') {
                                \$dir .= 'repositories';
                            } elseif (\$subNs === 'Entities') {
                                \$dir .= 'entities';
                            } elseif (\$subNs === 'Exporter') {
                                \$dir .= 'background-process/exporter';
                            } elseif (\$subNs === 'Importer') {
                                \$dir .= 'background-process/importer';
                            } elseif (\$subNs === 'GenericProcess') {
                                \$dir .= 'background-process/generic-process';
                            } elseif (\$subNs === 'Exposers') {
                                \$dir .= 'exposers';
                            } elseif (\$subNs === 'Mappers') {
                                \$dir .= 'mappers';
                            } elseif (\$subNs === 'Traits') {
                                \$dir .= 'traits';
                            } elseif (\$subNs === 'OAIPMHExpose') {
                                \$dir .= 'oaipmh';
                            } elseif (\$subNs === 'Cli') {
                                \$dir .= 'cli';
                            } else {
                                \$dir .= strtolower(\$subNs);
                            }

                            if (is_dir(\$dir)) {
                                \$nsSafe = str_replace('\\\\', '-', \$ns);
                                echo \$ns . '|' . \$dir . '|' . \$nsSafe . PHP_EOL;
                            }
                        }
                    }
                }
            }
        " | while IFS='|' read -r namespace ns_dir ns_safe; do
            if [ -n "$namespace" ] && [ -n "$ns_dir" ] && [ -d "$ns_dir" ]; then
                echo "    Generating namespace overview for: $namespace"
                "$VENDOR_BIN/php-class-diagram" "$ns_dir" \
                    > "$DOCS_DIR/diagrams/tainacan-namespace-$ns_safe.puml" 2>/dev/null || true
            fi
        done

        echo "  Step 3b: Generating root Tainacan namespace overview..."
        root_dirs="$SRC_DIR/classes $SRC_DIR/views"
        root_temp_dir="/tmp/tainacan-root-namespace-temp"
        rm -rf "$root_temp_dir"
        mkdir -p "$root_temp_dir"

        for root_dir in $root_dirs; do
            if [ -d "$root_dir" ]; then
                find "$root_dir" -maxdepth 1 -name "class-tainacan-*.php" -type f | while read -r php_file; do
                    if grep -q "^namespace Tainacan;" "$php_file" 2>/dev/null; then
                        cp "$php_file" "$root_temp_dir/" 2>/dev/null || true
                    fi
                done

                find "$root_dir" -mindepth 2 -maxdepth 2 -name "class-tainacan-*.php" -type f | while read -r php_file; do
                    if grep -q "^namespace Tainacan;" "$php_file" 2>/dev/null; then
                        cp "$php_file" "$root_temp_dir/" 2>/dev/null || true
                    fi
                done
            fi
        done

        if [ -d "$root_temp_dir" ] && [ "$(ls -A "$root_temp_dir" 2>/dev/null)" ]; then
            echo "    Generating root Tainacan namespace overview..."
            "$VENDOR_BIN/php-class-diagram" "$root_temp_dir" \
                > "$DOCS_DIR/diagrams/tainacan-namespace-Tainacan.puml" 2>/dev/null || true
            rm -rf "$root_temp_dir"
        else
            echo "    Warning: No root namespace classes found"
        fi

        echo "  Step 4: Generating per-class diagrams..."
        temp_diagrams_dir="/tmp/tainacan-diagrams-temp"
        mkdir -p "$temp_diagrams_dir"

        echo "    Generating diagrams for all classes..."
        class_count=0

        php -r "
            \$jsonFile = '/tmp/tainacan-classes-from-phpdoc.json';
            \$searchDirs = ['$SRC_DIR/classes', '$SRC_DIR/views'];

            if (!file_exists(\$jsonFile)) {
                fwrite(STDERR, 'ERROR: JSON file not found\n');
                exit(1);
            }

            \$data = json_decode(file_get_contents(\$jsonFile), true);
            if (\$data === null || !isset(\$data['classes'])) {
                fwrite(STDERR, 'ERROR: Invalid JSON or missing classes key\n');
                exit(1);
            }

            fwrite(STDERR, 'Found ' . count(\$data['classes']) . ' classes in JSON\n');
            \$foundCount = 0;

            foreach (\$data['classes'] as \$classInfo) {
                \$className = \$classInfo['name'];
                \$fileName = 'class-tainacan-' . strtolower(str_replace('_', '-', \$className)) . '.php';

                foreach (\$searchDirs as \$searchDir) {
                    if (!is_dir(\$searchDir)) {
                        continue;
                    }

                    \$iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator(\$searchDir, RecursiveDirectoryIterator::SKIP_DOTS)
                    );

                    foreach (\$iterator as \$file) {
                        if (\$file->isFile() && \$file->getFilename() === \$fileName) {
                            \$phpFile = \$file->getPathname();
                            \$classDir = dirname(\$phpFile);
                            \$tempFile = '/tmp/tainacan-diagrams-temp/' . \$className . '.puml';
                            echo \$classDir . '|' . \$tempFile . '|' . \$className . PHP_EOL;
                            \$foundCount++;
                            break 2;
                        }
                    }
                }
            }

            fwrite(STDERR, 'Found PHP files for ' . \$foundCount . ' classes\n');
        " > /tmp/php-find-output.txt 2> /tmp/php-find-errors.log

        if [ -f "/tmp/php-find-errors.log" ]; then
            echo "    $(cat /tmp/php-find-errors.log)"
        fi

        grep -E '^[^|]*\|[^|]*\|[^|]*$' /tmp/php-find-output.txt | sort -u | while IFS='|' read -r class_dir temp_file class_name; do
            if [ -d "$class_dir" ] && [ -n "$temp_file" ]; then
                "$VENDOR_BIN/php-class-diagram" "$class_dir" > "$temp_file" 2>/dev/null || true
                class_count=$((class_count + 1))
                if [ $((class_count % 10)) -eq 0 ]; then
                    echo "      Generated $class_count diagrams..."
                fi
            fi
        done
        echo "    Generated $class_count PlantUML files in temporary location"

        echo "  Step 4b: Converting to Mermaid and organizing by phpDocumentor structure..."
        if [ -f "$SCRIPTS_DIR/plantuml-to-mermaid.php" ]; then
            moved_count=0
            php -r "
                \$data = json_decode(file_get_contents('/tmp/tainacan-classes-from-phpdoc.json'), true);
                \$destBase = '$DOCS_DIR/phpdoc/classes';
                if (\$data && isset(\$data['classes'])) {
                    foreach (\$data['classes'] as \$classInfo) {
                        \$className = \$classInfo['name'];
                        \$mdRelDir = \$classInfo['mdRelDir'];
                        \$tempPuml = '/tmp/tainacan-diagrams-temp/' . \$className . '.puml';
                        \$tempMmd = '/tmp/tainacan-diagrams-temp/' . \$className . '.mmd';
                        \$destDir = \$destBase;
                        if (\$mdRelDir !== '.' && !empty(\$mdRelDir)) {
                            \$destDir .= '/' . \$mdRelDir;
                        }
                        \$destMmd = \$destDir . '/' . \$className . '.mmd';
                        if (!is_dir(\$destDir)) {
                            mkdir(\$destDir, 0755, true);
                        }
                        echo \$tempPuml . '|' . \$tempMmd . '|' . \$destMmd . PHP_EOL;
                    }
                }
            " | while IFS='|' read -r temp_puml temp_mmd dest_mmd; do
                if [ -f "$temp_puml" ] && [ -s "$temp_puml" ]; then
                    php "$SCRIPTS_DIR/plantuml-to-mermaid.php" "$temp_puml" > "$temp_mmd" 2>/dev/null || true

                    if [ -f "$temp_mmd" ] && [ -s "$temp_mmd" ]; then
                        class_name=$(basename "$dest_mmd" .mmd)

                        if [ -f "$SCRIPTS_DIR/filter-child-classes-from-mermaid.php" ]; then
                            php "$SCRIPTS_DIR/filter-child-classes-from-mermaid.php" \
                                "$temp_mmd" "$class_name" > "${temp_mmd}.filtered" 2>/dev/null || true

                            if [ -f "${temp_mmd}.filtered" ] && [ -s "${temp_mmd}.filtered" ]; then
                                mv "${temp_mmd}.filtered" "$temp_mmd"
                            fi
                        fi

                        cp "$temp_mmd" "$dest_mmd" 2>/dev/null || true
                        moved_count=$((moved_count + 1))
                    fi
                fi
            done
            echo "    Organized $moved_count diagrams next to their .md files"
        fi

        rm -rf "$temp_diagrams_dir"
        rm -f /tmp/tainacan-classes-from-phpdoc.json

        echo "  Step 4c: Inserting diagrams into .md files..."
        if [ -f "$SCRIPTS_DIR/insert-diagrams-into-md.php" ]; then
            php "$SCRIPTS_DIR/insert-diagrams-into-md.php" \
                "$DOCS_DIR/phpdoc/classes" \
                2>&1 | grep -E "(Processed|inserted|Warning|Error)" || true
        else
            echo "    Warning: insert-diagrams-into-md.php script not found"
        fi
    else
        echo -e "\e[33m    Warning: Namespace parser script not found\e[0m"
        echo "    Skipping namespace and per-class diagram generation"
    fi

    echo "  Step 5: Converting all PlantUML files to Mermaid format..."
    if [ -f "$SCRIPTS_DIR/plantuml-to-mermaid.php" ]; then
        find "$DOCS_DIR/diagrams" -name "*.puml" -type f | while read -r puml_file; do
            if [ -f "$puml_file" ] && [ -s "$puml_file" ]; then
                rel_path="${puml_file#$DOCS_DIR/diagrams/}"
                mmd_file="${rel_path%.puml}.mmd"
                mmd_path="$DOCS_DIR/diagrams/$mmd_file"
                mkdir -p "$(dirname "$mmd_path")"

                echo "    Converting $(basename "$puml_file")..."
                php "$SCRIPTS_DIR/plantuml-to-mermaid.php" "$puml_file" \
                    > "$mmd_path" 2>/dev/null || {
                    echo "      Warning: Failed to convert $puml_file to Mermaid"
                }

                if [ -f "$mmd_path" ] && [ -s "$mmd_path" ]; then
                    basename_mmd=$(basename "$mmd_path")
                    is_overview=false
                    if [ "$basename_mmd" = "tainacan-overview.mmd" ]; then
                        is_overview=true
                    elif [[ "$basename_mmd" =~ ^tainacan-namespace-.*\.mmd$ ]]; then
                        is_overview=true
                    fi

                    if [ "$is_overview" = true ] && [ -f "$SCRIPTS_DIR/remove-methods-attributes-from-mermaid.php" ]; then
                        php "$SCRIPTS_DIR/remove-methods-attributes-from-mermaid.php" "$mmd_path" \
                            > "${mmd_path}.tmp" 2>/dev/null && mv "${mmd_path}.tmp" "$mmd_path" || true
                    fi
                fi
            fi
        done
        echo "    Converted PlantUML files to Mermaid"
    else
        echo -e "\e[33m    Warning: PlantUML to Mermaid converter not found\e[0m"
    fi

    echo "  Step 6: Inserting namespace overview diagrams into Home.md..."
    if [ -f "$SCRIPTS_DIR/insert-namespace-diagrams-into-home.php" ] && \
       [ -f "$DOCS_DIR/phpdoc/Home.md" ]; then
        php "$SCRIPTS_DIR/insert-namespace-diagrams-into-home.php" \
            "$DOCS_DIR/phpdoc/Home.md" \
            "$DOCS_DIR/diagrams" \
            2>&1 | grep -E "(Inserted|Warning|Error)" || true
    else
        echo "    Warning: insert-namespace-diagrams-into-home.php script not found or Home.md missing"
    fi

    puml_count=$(find "$DOCS_DIR/diagrams" -name "*.puml" -type f -size +0 2>/dev/null | wc -l)
    mmd_count=$(find "$DOCS_DIR/diagrams" -name "*.mmd" -type f -size +0 2>/dev/null | wc -l)

    if [ "$puml_count" -gt 0 ] || [ "$mmd_count" -gt 0 ]; then
        echo -e "\e[32m ### Diagrams generated: $puml_count PlantUML files, $mmd_count Mermaid files ### \e[0m"
        echo "  Diagrams saved to: $DOCS_DIR/diagrams/"
    else
        echo -e "\e[33m ### Warning: No diagrams were generated ### \e[0m"
    fi
else
    echo -e "\e[33m ### php-class-diagram tool not found ### \e[0m"
    echo "  Run: cd $PLUGIN_ROOT && composer install"
fi

echo -e "\e[32m ### Documentation generation completed! ### \e[0m"
