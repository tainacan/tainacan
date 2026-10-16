#!/usr/bin/env node

/**
 * Points git at the versioned hooks in .githooks/ (run by the npm `prepare` script).
 *
 * This is a convenience, so it must never fail an install: outside a git checkout
 * (release zip, Docker build without .git, "dubious ownership" errors) it just exits.
 * A core.hooksPath the developer already set for another purpose is left untouched.
 */

/* eslint-disable no-console */
/* global require */

const { spawnSync } = require('child_process');
const fs = require('fs');

const HOOKS_DIR = '.githooks';

function git(gitArgs) {
    return spawnSync('git', gitArgs, { encoding: 'utf8' });
}

function main() {
    if (!fs.existsSync('.git')) {
        return;
    }

    const current = git(['config', '--get', 'core.hooksPath']);
    const currentPath = current.status === 0 ? current.stdout.trim() : '';

    if (currentPath === HOOKS_DIR) {
        return;
    }

    if (currentPath) {
        console.log(`ℹ️  core.hooksPath is already "${currentPath}", leaving it alone. Tainacan hooks live in ${HOOKS_DIR}/.`);
        return;
    }

    if (git(['config', 'core.hooksPath', HOOKS_DIR]).status !== 0) {
        console.log(`ℹ️  Could not enable the Tainacan git hooks. Run \`git config core.hooksPath ${HOOKS_DIR}\` manually if you want them.`);
        return;
    }

    console.log(`✅ Git hooks enabled from ${HOOKS_DIR}/ (bypass a push check with \`git push --no-verify\`).`);
}

main();
