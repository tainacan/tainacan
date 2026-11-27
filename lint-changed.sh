#!/bin/bash

# Tainacan - Incremental lint runner.
#
# Usage:
#   ./lint-changed.sh
#   ./lint-changed.sh --base=<ref> --head=<ref>
#   ./lint-changed.sh --working-tree
#   ./lint-changed.sh --phpcs
#   ./lint-changed.sh -- path/to/file.php
#
# Exit codes:
#   0 - checks passed
#   1 - blocking lint errors
#   2 - Git, dependency, or argument error

unset base_ref head_ref
use_phpcs=false
working_tree=false
files=()
php_files=()
frontend_files=()

die() {
    printf '%s\n' "$1" >&2
    exit 2
}

while (($#)); do
    case "$1" in
        --base=*) base_ref=${1#--base=} ;;
        --head=*) head_ref=${1#--head=} ;;
        --working-tree) working_tree=true ;;
        --phpcs) use_phpcs=true ;;
        --)
            shift
            files+=("$@")
            break
            ;;
        -*) die "Unknown option: $1" ;;
        *) files+=("$1") ;;
    esac
    shift
done

root=$(git rev-parse --show-toplevel 2>/dev/null) || die "Not inside a Git repository."
cd "$root" || exit 2

resolve_commit() {
    git rev-parse --verify --quiet --end-of-options "$1^{commit}"
}

from_commit=false
temp_dirs=()
cleanup() {
    if ((${#temp_dirs[@]})); then
        cd "$root" || return
        rm -rf -- "${temp_dirs[@]}"
    fi
}
trap cleanup EXIT

if [[ ${head_ref+x} ]] && { [[ "$working_tree" == true ]] || ((${#files[@]})); }; then
    die "--head cannot be combined with --working-tree or explicit file paths."
fi

if ((${#files[@]} == 0)); then
    if [[ ${base_ref+x} ]]; then
        base=$(resolve_commit "$base_ref") || die "Invalid base ref: $base_ref"
    else
        for base_ref in upstream/develop origin/develop develop; do
            if base=$(resolve_commit "$base_ref"); then
                break
            fi
        done
        [[ -n "$base" ]] || die "No default base ref found. Fetch develop or specify --base=<ref>."
    fi

    head_ref=${head_ref-HEAD}
    head=$(resolve_commit "$head_ref") || die "Invalid head ref: $head_ref"
    temp_root=$(mktemp -d) || die "Could not create temporary directory."
    temp_dirs+=("$temp_root")
    diff_file="$temp_root/paths"

    git diff --raw --no-renames -z --diff-filter=d "$base...$head" -- > "$diff_file" ||
        die "Could not diff $head_ref against $base_ref."

    while IFS= read -r -d '' change && IFS= read -r -d '' file; do
        # The destination mode distinguishes regular files from symlinks and submodules.
        read -r _ mode _ <<< "$change"
        [[ "$mode" == 100644 || "$mode" == 100755 ]] && files+=("$file")
    done < "$diff_file"

    if [[ "$working_tree" == false ]]; then
        from_commit=true
    else
        # Add staged, unstaged and untracked files to the committed diff.
        git diff --name-only -z --diff-filter=d "$head" -- > "$diff_file" ||
            die "Could not read working-tree changes."
        git ls-files --others --exclude-standard -z >> "$diff_file" ||
            die "Could not read untracked files."

        while IFS= read -r -d '' file; do
            files+=("$file")
        done < "$diff_file"
    fi
fi

unique_files=()
for file in "${files[@]}"; do
    if [[ "$from_commit" == false && ! -f "$file" ]]; then
        continue
    fi

    file=${file#./}
    case "$file" in
        *.php|src/views/*.js|src/views/*.vue) ;;
        *) continue ;;
    esac

    for existing in "${unique_files[@]}"; do
        [[ "$file" == "$existing" ]] && continue 2
    done
    unique_files+=("$file")

    # Prefix paths with ./ so names starting with "-" are not options.
    case "$file" in
        *.php) php_files+=("./$file") ;;
        *) frontend_files+=("./$file") ;;
    esac
done

if ((${#php_files[@]} == 0 && ${#frontend_files[@]} == 0)); then
    echo "No changed PHP, JS or Vue files to lint."
    exit 0
fi

if ((${#php_files[@]})) && ! command -v php >/dev/null 2>&1; then
    die "PHP CLI not found. Install PHP or add it to PATH to lint changed PHP files."
fi

eslint_bin="$root/node_modules/eslint/bin/eslint.js"
eslint_options=()
phpcs_bin="$root/src/vendor/bin/phpcs"

copy_committed_file() {
    local file="./${1#./}" destination=$2
    mkdir -p -- "$destination/${file%/*}" &&
        git cat-file blob "$head:${file#./}" > "$destination/$file" ||
        die "Could not read $file from $head_ref."
}

if [[ "$from_commit" == true ]]; then
    snapshot="$temp_root/tree"
    if ((${#frontend_files[@]})) && [[ -f "$eslint_bin" ]]; then
        # Put the committed ESLint config near the installed npm modules
        # so its imports resolve on both Unix and Windows.
        config_dir=$(mktemp -d "$root/node_modules/.tainacan-lint.XXXXXXXX") ||
            die "Could not create temporary ESLint directory."
        temp_dirs+=("$config_dir")
        copy_committed_file eslint.config.js "$config_dir"
        if git cat-file -e "$head:package.json" 2>/dev/null; then
            copy_committed_file package.json "$config_dir"
        fi
        eslint_options=(--config "$config_dir/eslint.config.js")
    fi

    if [[ "$use_phpcs" == true && -f "$phpcs_bin" ]] && ((${#php_files[@]})) &&
        git cat-file -e "$head:phpcs.xml.dist" 2>/dev/null; then
        copy_committed_file phpcs.xml.dist "$snapshot"
    fi

    for file in "${php_files[@]}" "${frontend_files[@]}"; do
        copy_committed_file "$file" "$snapshot"
    done
    cd "$snapshot" || exit 2
fi

run_batch() {
    local mode=$1
    shift

    case "$mode" in
        eslint)
            node "$eslint_bin" "${eslint_options[@]}" --format=json -- "$@"
            ;;
        phpcs)
            php "$phpcs_bin" --standard=phpcs.xml.dist --warning-severity=0 -s "$@"
            ;;
    esac
}

# Limit both the number of arguments and the estimated command-line size.
run_batches() {
    local mode=$1
    shift
    local -a batch=()
    local file estimate size=0 status=0

    for file in "$@"; do
        estimate=$((${#file} * 2 + 4))
        if ((${#batch[@]} > 0 && (${#batch[@]} >= 20 || size + estimate > 6000))); then
            run_batch "$mode" "${batch[@]}" || status=1
            batch=()
            size=0
        fi

        batch+=("$file")
        size=$((size + estimate))
    done

    if ((${#batch[@]})); then
        run_batch "$mode" "${batch[@]}" || status=1
    fi
    return "$status"
}

failed=false
if ((${#php_files[@]})); then
    printf '\nPHP syntax: %s file(s)\n' "${#php_files[@]}"

    for file in "${php_files[@]}"; do
        php -d display_errors=1 -d log_errors=0 -l "$file" || failed=true
    done
fi

if ((${#frontend_files[@]})); then
    printf '\nESLint: %s file(s)\n' "${#frontend_files[@]}"

    if ! command -v node >/dev/null 2>&1 || [[ ! -f "$eslint_bin" ]]; then
        echo "Node.js or ESLint unavailable; install npm dependencies." >&2
        failed=true
    else
        run_batches eslint "${frontend_files[@]}" || failed=true
    fi
fi

if [[ "$use_phpcs" == true ]] && ((${#php_files[@]})); then
    printf '\nPHPCS (informational): %s file(s)\n' "${#php_files[@]}"

    if [[ -f "$phpcs_bin" ]]; then
        run_batches phpcs "${php_files[@]}" || true
    else
        echo "PHPCS unavailable; skipping PHPCS report."
    fi
fi

if [[ "$failed" == true ]]; then
    echo "Lint failed." >&2
    exit 1
fi

echo "Lint passed."
