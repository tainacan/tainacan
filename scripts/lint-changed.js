#!/usr/bin/env node

/**
 * Tainacan - Incremental lint runner.
 *
 * Lints only the files changed in the current branch, so legacy code that was
 * never touched does not block anyone. Used by:
 *  - .githooks/pre-push
 *  - .github/workflows/lint.yml
 *  - npm run lint:changed
 *
 * What blocks (exit code 1):
 *  - `php -l` syntax errors in changed .php files.
 *  - ESLint *errors* in changed .js/.vue files under src/views/ (warnings never block).
 *
 * What never blocks:
 *  - PHPCS, which is opt-in via --phpcs and only reports. The PHPCS backlog is
 *    still being triaged (see issue #1052), so enforcing it would block almost
 *    every PR that touches a legacy PHP file.
 *
 * Exit codes: 0 = passed, 1 = lint errors found, 2 = could not work out which files changed.
 *
 * Usage:
 *  node scripts/lint-changed.js                    Files changed vs. upstream/develop, origin/develop or develop
 *  node scripts/lint-changed.js --base=<git-ref>   Files changed vs. the given ref (merge-base diff)
 *  node scripts/lint-changed.js --working-tree     Also include uncommitted and untracked files
 *  node scripts/lint-changed.js --phpcs            Also report PHPCS findings (never fails)
 *  node scripts/lint-changed.js <file> [<file>]    Lint exactly these files
 *
 * To add a linter, append an entry to LINTERS: no other code needs to change.
 *
 * Processes are spawned without a shell, so file names coming from a diff can
 * never be interpreted as shell syntax.
 */

/* eslint-disable no-console */
/* global process, require */

const { spawnSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const EXIT_OK = 0;
const EXIT_LINT_ERRORS = 1;
const EXIT_NO_DIFF = 2;

// Keeps each spawned command below the Windows command line limit (~8191 chars).
const MAX_COMMAND_CHARS = 6000;
const BASE_REF_CANDIDATES = ['upstream/develop', 'origin/develop', 'develop'];
const PHPCS_BIN = path.join('src', 'vendor', 'bin', 'phpcs');

/* ------------------------------------------------------------------ */
/* Command line                                                        */
/* ------------------------------------------------------------------ */

function parseArguments(argv) {
    const options = { base: null, phpcs: false, workingTree: false, files: [] };

    for (const arg of argv) {
        if (arg.startsWith('--base=')) {
            options.base = arg.slice('--base='.length);
        } else if (arg === '--phpcs') {
            options.phpcs = true;
        } else if (arg === '--working-tree') {
            options.workingTree = true;
        } else if (!arg.startsWith('-')) {
            options.files.push(arg);
        }
    }

    return options;
}

/* ------------------------------------------------------------------ */
/* Git: which files changed?                                           */
/* ------------------------------------------------------------------ */

/**
 * Runs git and returns its trimmed stdout, or null if git failed.
 */
function git(gitArgs) {
    const result = spawnSync('git', gitArgs, { encoding: 'utf8' });
    return result.status === 0 ? result.stdout.trim() : null;
}

function splitLines(output) {
    return output ? output.split('\n').map((line) => line.trim()).filter(Boolean) : [];
}

function refExists(ref) {
    return git(['rev-parse', '--verify', '--quiet', ref]) !== null;
}

/**
 * Picks the ref the current branch is compared against. Exits if an explicit ref is invalid,
 * so a typo or a shallow checkout can never be mistaken for "nothing changed".
 */
function resolveBaseRef(explicitBase) {
    if (!explicitBase) {
        return BASE_REF_CANDIDATES.find(refExists) || null;
    }

    if (!refExists(explicitBase)) {
        console.error(`❌ Base ref "${explicitBase}" does not exist. Did the checkout fetch the full history?`);
        process.exit(EXIT_NO_DIFF);
    }

    return explicitBase;
}

function listCommittedChanges(base) {
    const output = git(['diff', '--name-only', '--diff-filter=d', `${base}...HEAD`]);

    if (output === null) {
        console.error(`❌ Could not diff against "${base}".`);
        process.exit(EXIT_NO_DIFF);
    }

    return splitLines(output);
}

function listWorkingTreeChanges() {
    return [
        ...splitLines(git(['diff', '--name-only', '--diff-filter=d', 'HEAD'])),
        ...splitLines(git(['ls-files', '--others', '--exclude-standard']))
    ];
}

function getChangedFiles(options) {
    let files = options.files;

    if (files.length === 0) {
        const base = resolveBaseRef(options.base);
        files = base ? listCommittedChanges(base) : [];

        if (options.workingTree) {
            files = files.concat(listWorkingTreeChanges());
        }
    }

    const normalized = files.map((file) => file.replace(/\\/g, '/'));

    return Array.from(new Set(normalized)).filter((file) => fs.existsSync(file));
}

/* ------------------------------------------------------------------ */
/* Running processes                                                   */
/* ------------------------------------------------------------------ */

function commandExists(command) {
    const probe = process.platform === 'win32' ? 'where' : 'which';
    return spawnSync(probe, [command], { stdio: 'ignore' }).status === 0;
}

/**
 * Splits files into groups whose joined length fits in a single command line.
 */
function chunkFiles(files) {
    const chunks = [];
    let current = [];
    let length = 0;

    for (const file of files) {
        if (current.length > 0 && length + file.length + 1 > MAX_COMMAND_CHARS) {
            chunks.push(current);
            current = [];
            length = 0;
        }
        current.push(file);
        length += file.length + 1;
    }

    if (current.length > 0) {
        chunks.push(current);
    }

    return chunks;
}

/**
 * Runs `command baseArgs... files...` once per chunk. Returns true only if every run succeeds.
 */
function runInChunks(command, baseArgs, files) {
    let passed = true;

    for (const chunk of chunkFiles(files)) {
        const result = spawnSync(command, [...baseArgs, ...chunk], { stdio: 'inherit' });
        if (result.status !== 0) {
            passed = false;
        }
    }

    return passed;
}

/**
 * Locates ESLint's own entry point so it can run through `node`, with no `npx` startup cost.
 */
function findEslintBin() {
    try {
        const packageRoot = path.dirname(require.resolve('eslint/package.json'));
        return path.join(packageRoot, 'bin', 'eslint.js');
    } catch {
        return null;
    }
}

/* ------------------------------------------------------------------ */
/* Linters                                                             */
/* ------------------------------------------------------------------ */

/**
 * A linter is { name, enabled(options), accepts(file), run(files) }.
 * run() returns true when the files are acceptable.
 */
const LINTERS = [
    {
        name: 'PHP syntax (php -l)',
        enabled: () => true,
        accepts: (file) => file.endsWith('.php'),
        run(files) {
            if (!commandExists('php')) {
                console.log('ℹ️  "php" is not in PATH, skipping the PHP syntax check (CI still runs it).');
                return true;
            }

            let passed = true;
            for (const file of files) {
                // log_errors=0 stops PHP from printing every parse error twice.
                const args = ['-d', 'display_errors=1', '-d', 'log_errors=0', '-l', file];
                if (spawnSync('php', args, { stdio: 'inherit' }).status !== 0) {
                    passed = false;
                }
            }
            return passed;
        }
    },
    {
        name: 'ESLint (errors only)',
        enabled: () => true,
        accepts: (file) => /^src\/views\/.*\.(js|vue)$/.test(file),
        run(files) {
            const eslintBin = findEslintBin();
            if (!eslintBin) {
                console.error('❌ ESLint is not installed. Run `npm install` first.');
                return false;
            }

            // --quiet drops warnings, so only errors can fail the run.
            return runInChunks(process.execPath, [eslintBin, '--quiet'], files);
        }
    },
    {
        name: 'PHPCS (informational)',
        enabled: (options) => options.phpcs,
        accepts: (file) => file.endsWith('.php'),
        run(files) {
            if (!commandExists('php') || !fs.existsSync(PHPCS_BIN)) {
                console.log('ℹ️  PHP or PHPCS is not available (run `composer install`), skipping the PHPCS report.');
                return true;
            }

            runInChunks('php', [PHPCS_BIN, '--standard=phpcs.xml.dist', '--warning-severity=0', '-s'], files);
            // Reporting only: findings are shown but never fail the check.
            return true;
        }
    }
];

/* ------------------------------------------------------------------ */
/* Main                                                                */
/* ------------------------------------------------------------------ */

function main() {
    const options = parseArguments(process.argv.slice(2));
    const changedFiles = getChangedFiles(options);

    let linted = 0;
    let failed = false;

    for (const linter of LINTERS.filter((candidate) => candidate.enabled(options))) {
        const files = changedFiles.filter(linter.accepts);
        if (files.length === 0) {
            continue;
        }

        linted += files.length;
        console.log(`\n▶ ${linter.name}: ${files.length} file(s)`);
        if (!linter.run(files)) {
            failed = true;
        }
    }

    if (linted === 0) {
        console.log('✅ No changed PHP, JS or Vue files to lint.');
        return EXIT_OK;
    }

    if (failed) {
        console.error('\n❌ Lint failed. Fix the errors above, or bypass locally with `git push --no-verify`.');
        return EXIT_LINT_ERRORS;
    }

    console.log('\n✨ Lint passed.');
    return EXIT_OK;
}

process.exit(main());
