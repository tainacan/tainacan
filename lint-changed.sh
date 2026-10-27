#!/bin/bash

# Tainacan - Incremental lint runner.
#
# Usage:
#   ./lint-changed.sh
#   ./lint-changed.sh --base=origin/develop
#   ./lint-changed.sh --working-tree
#   ./lint-changed.sh --phpcs
#   ./lint-changed.sh -- path/to/file.php
#
# Exit codes:
#   0 - checks passed
#   1 - blocking lint errors
#   2 - Git or argument error

base=""
use_phpcs=false
working_tree=false

files=()
php_files=()
frontend_files=()

failed=false

# Read command-line arguments.
while (($#)); do
    case "$1" in
        --base=*)
            base=${1#--base=}
            ;;

        --working-tree)
            working_tree=true
            ;;

        --phpcs)
            use_phpcs=true
            ;;

        --)
            shift
            files+=("$@")
            break
            ;;

        -*)
            printf 'Unknown option: %s\n' "$1" >&2
            exit 2
            ;;

        *)
            files+=("$1")
            ;;
    esac

    shift
done

# Run from the repository root.
root=$(git rev-parse --show-toplevel 2>/dev/null) || {
    echo "Not inside a Git repository." >&2
    exit 2
}

cd "$root" || exit 2

# If files were not explicitly provided, obtain them from Git.
if ((${#files[@]} == 0)); then

    # Validate an explicitly supplied base.
    if [[ -n "$base" ]]; then
        if ! git rev-parse \
            --verify \
            --quiet \
            --end-of-options \
            "$base^{commit}" >/dev/null; then

            printf 'Invalid base ref: %s\n' "$base" >&2
            exit 2
        fi

    # Otherwise, use the same default candidates as the original script.
    else
        for ref in upstream/develop origin/develop develop; do
            if git rev-parse --verify --quiet "$ref^{commit}" >/dev/null; then
                base=$ref
                break
            fi
        done
    fi

    tmp=$(mktemp) || {
        echo "Could not create temporary file." >&2
        exit 2
    }

    trap 'rm -f -- "$tmp"' EXIT

    if [[ -n "$base" ]]; then
        # Three dots compare HEAD with the merge base.
        if ! git diff \
            --name-only \
            -z \
            --diff-filter=d \
            "$base...HEAD" \
            -- > "$tmp"; then

            printf 'Could not diff against %s\n' "$base" >&2
            exit 2
        fi

        while IFS= read -r -d '' file; do
            files+=("$file")
        done < "$tmp"
    else
        echo "No default base ref found; committed changes will not be checked." >&2
    fi

    if [[ "$working_tree" == true ]]; then

        # Staged and unstaged changes relative to HEAD.
        if ! git diff \
            --name-only \
            -z \
            --diff-filter=d \
            HEAD \
            -- > "$tmp"; then

            echo "Could not read working-tree changes." >&2
            exit 2
        fi

        while IFS= read -r -d '' file; do
            files+=("$file")
        done < "$tmp"

        # Untracked files, excluding ignored files.
        if ! git ls-files \
            --others \
            --exclude-standard \
            -z > "$tmp"; then

            echo "Could not read untracked files." >&2
            exit 2
        fi

        while IFS= read -r -d '' file; do
            files+=("$file")
        done < "$tmp"
    fi
fi

# Keep each path as a separate argument and avoid duplicate checks.
unique_files=()

for file in "${files[@]}"; do
    # A deleted file must not be sent to a linter.
    [[ -f "$file" ]] || continue

    duplicate=false

    for existing in "${unique_files[@]}"; do
        if [[ "$file" == "$existing" ]]; then
            duplicate=true
            break
        fi
    done

    [[ "$duplicate" == true ]] && continue

    unique_files+=("$file")

    # Prefix paths with ./ so names starting with "-" are not options.
    case "$file" in
        *.php)
            php_files+=("./${file#./}")
            ;;

        src/views/*.js|src/views/*.vue|./src/views/*.js|./src/views/*.vue)
            frontend_files+=("./${file#./}")
            ;;
    esac
done

# Run a linter for one batch.
run_batch() {
    local mode=$1
    shift

    case "$mode" in
        eslint)
            node "$eslint_bin" --quiet -- "$@"
            ;;

        phpcs)
            # Paths have a ./ prefix, so they cannot be parsed as options.
            php "$phpcs_bin" \
                --standard=phpcs.xml.dist \
                --warning-severity=0 \
                -s \
                "$@"
            ;;
    esac
}

# Divide files into batches to avoid excessively long command lines.
run_batches() {
    local mode=$1
    local file
    local count=0
    local size=0
    local estimate

    shift

    local -a batch=()

    for file in "$@"; do
        # Leave extra room for quoting and command options.
        estimate=$(( ${#file} * 2 + 4 ))

        if ((count > 0 && (count >= 20 || size + estimate > 6000))); then
            run_batch "$mode" "${batch[@]}" || batch_failed=true

            batch=()
            count=0
            size=0
        fi

        batch+=("$file")
        count=$((count + 1))
        size=$((size + estimate))
    done

    if ((count > 0)); then
        run_batch "$mode" "${batch[@]}" || batch_failed=true
    fi
}

# PHP syntax check.
if ((${#php_files[@]})); then
    printf '\nPHP syntax: %s file(s)\n' "${#php_files[@]}"

    if command -v php >/dev/null 2>&1; then
        for file in "${php_files[@]}"; do
            if ! php \
                -d display_errors=1 \
                -d log_errors=0 \
                -l \
                "$file"; then

                failed=true
            fi
        done
    else
        echo "PHP not found; skipping PHP syntax check (CI still runs it)."
    fi
fi

# ESLint checks changed JavaScript and Vue files under src/views/.
if ((${#frontend_files[@]})); then
    printf '\nESLint: %s file(s)\n' "${#frontend_files[@]}"

    eslint_bin="node_modules/eslint/bin/eslint.js"

    if ! command -v node >/dev/null 2>&1 || [[ ! -f "$eslint_bin" ]]; then
        echo "Node.js or ESLint unavailable; install npm dependencies." >&2
        failed=true
    else
        batch_failed=false

        run_batches eslint "${frontend_files[@]}"

        if [[ "$batch_failed" == true ]]; then
            failed=true
        fi
    fi
fi

# PHPCS is optional and informational.
if [[ "$use_phpcs" == true ]] && ((${#php_files[@]})); then
    printf '\nPHPCS (informational): %s file(s)\n' "${#php_files[@]}"

    phpcs_bin="src/vendor/bin/phpcs"

    if command -v php >/dev/null 2>&1 && [[ -f "$phpcs_bin" ]]; then
        batch_failed=false
        run_batches phpcs "${php_files[@]}"
    else
        echo "PHP or PHPCS unavailable; skipping PHPCS report."
    fi
fi

if ((${#php_files[@]} == 0 && ${#frontend_files[@]} == 0)); then
    echo "No changed PHP, JS or Vue files to lint."
    exit 0
fi

if [[ "$failed" == true ]]; then
    echo "Lint failed." >&2
    exit 1
fi

echo "Lint passed."
exit 0