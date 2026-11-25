#!/bin/bash
set -e

root=$(cd "$(dirname "$0")/.." && pwd)
tmp=$(mktemp -d)
trap 'cd "$root" && rm -rf -- "$tmp"' EXIT

git init -q --bare "$tmp/remote.git"
git init -q -b develop "$tmp/local"
cd "$tmp/local"
git config user.name "Tainacan lint test"
git config user.email "lint-test@example.invalid"
git config commit.gpgsign false
git config core.autocrlf false
git config core.hooksPath .githooks
git remote add origin "$tmp/remote.git"

mkdir -p .githooks src/views
cp "$root/.githooks/pre-push" .githooks/
cp "$root/lint-changed.sh" "$root/eslint.config.js" "$root/package.json" .
ln -s "$root/node_modules" node_modules
git add .githooks lint-changed.sh eslint.config.js package.json
git commit -qm "Create lint test fixture"
git push -q origin develop:refs/heads/clean

expect_failure() {
    local expected=$1
    shift
    local output

    if output=$("$@" 2>&1); then
        printf 'Expected failure from %s\n%s\n' "$*" "$output" >&2
        exit 1
    fi
    printf '%s\n' "$output"
    if [[ "$output" != *"$expected"* ]]; then
        printf 'Expected output to contain: %s\n' "$expected" >&2
        exit 1
    fi
}

git switch -qc hotfix/review
printf 'const = ;\n' > src/views/legacy.js
git add src/views/legacy.js
git commit -qm "Simulate an existing remote tip with a legacy lint error"
git push -q --no-verify origin hotfix/review

php_file='./- review.php'
js_file='./src/views/[review] test.js'
vue_file='./src/views/ReviewFixture.vue'
printf '<?php echo "ok";\n' > "$php_file"
printf 'console.log("warning only");\n' > "$js_file"
printf '<template>\n    <div />\n</template>\n' > "$vue_file"
git add -- "$php_file" "$js_file" "$vue_file"
git commit -qm "Commit valid PHP, Vue and warning-only JavaScript"
printf '<?php if (\n' > "$php_file"
printf 'const = ;\n' > "$js_file"
printf '<script>const = ;</script>\n' > "$vue_file"
printf 'throw new Error("Uncommitted ESLint config");\n' > eslint.config.js
output=$(git push -q origin hotfix/review 2>&1) || {
    printf '%s\n' "$output" >&2
    exit 1
}
printf '%s\n' "$output"
[[ "$output" == *'No syntax errors detected'* && "$output" == *'warning'*no-console* ]]

git add -- "$php_file" "$js_file" "$vue_file"
git commit -qm "Commit invalid PHP, JavaScript and Vue"
printf '<?php echo "ok";\n' > "$php_file"
printf 'console.log("warning only");\n' > "$js_file"
printf '<template>\n    <div />\n</template>\n' > "$vue_file"
output=$(expect_failure 'Push aborted' git push -q origin hotfix/review)
printf '%s\n' "$output"
[[ "$output" == *'Parse error'* && "$output" == *'Parsing error'* && "$output" == *ReviewFixture.vue* ]]

# Push multiple refs from another checkout, with a clean update before the bad ref.
git switch -qf develop
clean_tip=$(git rev-parse HEAD)
git commit -qm "Advance the clean ref" --allow-empty
output=$(expect_failure 'Parsing error' git push -q origin develop:refs/heads/clean hotfix/review)
printf '%s\n' "$output"
[[ "$output" == *'linting refs/heads/develop -> refs/heads/clean'* ]]
[[ "$(git ls-remote origin refs/heads/clean)" == "$clean_tip"$'\trefs/heads/clean' ]]

git switch -q --detach
git branch -D develop
git push -q origin --delete hotfix/review
expect_failure 'No default base ref found' git push -q origin HEAD:refs/heads/new
status=0
./lint-changed.sh --base=missing || status=$?
[[ "$status" == 2 ]]

echo "Lint integration tests passed."
