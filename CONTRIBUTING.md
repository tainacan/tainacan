# Contributing to Tainacan

Thank you for your interest in contributing to Tainacan! This document explains our workflow, conventions, and how to get your contributions merged efficiently.

## Code of Conduct

As with all Tainacan projects, we want to ensure a welcoming environment for everyone. With that in mind, all contributors are expected to follow the [WordPress Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/).

## Getting Started

1. Fork this repository to your GitHub account.

2. Clone your fork locally:

```shell
git clone https://github.com/your-username/tainacan.git
cd tainacan
```

3. Add the upstream remote:

```shell
git remote add upstream https://github.com/tainacan/tainacan.git
```

4. Install dependencies as described in [our Developers Wiki](https://tainacan.github.io/tainacan-wiki/#/dev/setup-local).

## GitFlow Workflow

This project aims to follow the [GitFlow branching model](https://danielkummer.github.io/git-flow-cheatsheet/). Please read it carefully before contributing.

### Permanent Branches

| **Branch** | **Purpose** |
|---|---|
| `main` | Production-ready code. Only receives merges from `release/*` and `hotfix/*`. |
| `develop` | Main integration branch. All features and fixes are merged here first. |

**Never commit directly to `main` or `develop`. All changes must go through a Pull Request.**

### Supporting Branches

#### feature/*

Used to develop new features or non-urgent bug fixes.

```shell
# Always branch off from: develop
git checkout develop
git pull upstream develop
git checkout -b feature/your-feature-name
```

- Merge back into: `develop`
- Naming: `feature/<short-description>` (e.g., `feature/user-authentication`)

#### release/*

Used to prepare a new production release. Allows last-minute bug fixes and version bumping.

```shell
# Always branch off from: develop
git checkout develop
git pull upstream develop
git checkout -b release/1.2.0
```

- Merge back into: `main` and `develop`
- Naming: `release/<version>` (e.g., `release/1.2.0`)
- Only maintainers create release branches. Contributors should open an issue or a PR against `develop` instead.

#### hotfix/*

Used to quickly patch a critical bug in production.

```shell
# Always branch off from: main
git checkout main
git pull upstream main
git checkout -b hotfix/critical-bug-description
```

- Merge back into: `main` and `develop`
- Naming: `hotfix/<short-description>` (e.g., `hotfix/fix-login-crash`)
- Only maintainers create hotfix branches. Contributors should open an issue for critical production bugs.

### Visual Overview

```
main ─────────────────────────────────────────────────► (production)
       ↑                              ↑
   release/1.0.0              hotfix/critical-fix
       ↑                              ↑
develop ──────────────────────────────────────────────► (integration)
       ↑            ↑           ↑
  feature/A    feature/B    feature/C
```

Non-urgent bug fixes also use the `feature/` prefix (there is no separate `bugfix/` branch type).

## Branch Naming Convention

| **Type** | **Pattern** | **Example** |
|---|---|---|
| Feature | `feature/<description>` | `feature/geo-filter` |
| Hotfix | `hotfix/<description>` | `hotfix/security-patch` |
| Release | `release/<version>` | `release/2.0.0` |

Use lowercase letters, numbers, and hyphens only. Keep it short and descriptive.

## Commit Messages

We try to follow the [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Format

```
<type>(optional scope): <short description>

[optional body]

[optional footer(s)]
```

### Types

| **Type** | **When to use** |
|---|---|
| `feat` | A new feature |
| `fix` | A bug fix |
| `docs` | Documentation changes only |
| `style` | Formatting, missing semicolons, etc. (no logic change) |
| `refactor` | Code change that neither fixes a bug nor adds a feature |
| `test` | Adding or updating tests |
| `chore` | Build process, dependency updates, tooling |
| `perf` | Performance improvements |
| `ci` | CI/CD configuration changes |

#### Examples

```
feat(auth): add OAuth2 login with Google
fix(api): handle null response on user endpoint
docs(readme): update installation steps
chore(deps): bump lodash to 4.17.21
```

Keep the subject line under 72 characters. Use the body to explain why, not what.

## Opening a Pull Request

1. Sync your branch with the latest `develop` before opening a PR:

```shell
git fetch upstream
git rebase upstream/develop
```

2. Push your branch to your fork:

```shell
git push origin feature/your-feature-name
```

3. Open a Pull Request against `develop` (not `main`).

4. Fill in the PR template, including:

   a. A clear description of the changes

   b. The related issue number (e.g., `Closes #42`)

   c. Screenshots or recordings, if applicable

   d. Any relevant testing instructions

5. Ensure all CI checks pass before requesting a review.

### PR Checklist

- [ ] My code follows the project's style guidelines
- [ ] I have added/updated tests for my changes
- [ ] All existing tests pass locally
- [ ] I have updated the documentation if needed
- [ ] My branch is up to date with `develop`
- [ ] The PR description clearly explains the changes

### Code Review

- At least one approval from a maintainer is required before merging.
- Address all review comments before requesting a re-review.
- Maintainers may request changes, approve, or close PRs.
- Be respectful and constructive in all review discussions.
- PRs without activity for 30 days may be closed by maintainers.

## Reporting Bugs

Before opening a bug report:

- Search [existing issues](https://github.com/tainacan/tainacan/issues) to avoid duplicates.
- Reproduce the bug on the latest version.

When opening an issue, please include:

- A clear and descriptive title
- Steps to reproduce the problem
- Expected vs. actual behavior
- Screenshots or logs if applicable
- Your environment (OS, browser, runtime version, etc.)

## Suggesting Features

Feature requests are welcome! Please:

- Check if the feature has already been requested in [issues](https://github.com/tainacan/tainacan/issues).
- Open a new issue with the label `enhancement`.
- Describe the problem your feature would solve.
- Provide examples of the expected behavior.

Large features should be discussed in an issue before starting development to avoid wasted effort.

## Coding Guidelines

We're still working on adopting more strong rulesets for our coding guidelines. The adoption of automation tools is under implementation as of https://github.com/tainacan/tainacan/issues/1052.

- **PHP:** Follow the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/). Lint with PHPCS using the ruleset in `phpcs.xml.dist` (`WordPress-Core` and `WordPress-Docs`).
- **JavaScript / Vue:** Follow the project's ESLint configuration in `eslint.config.js`. ESLint also runs during the webpack build.
- Prefer matching the style of the surrounding code when editing an existing file.

For local setup details, see the [Developers Wiki](https://tainacan.github.io/tainacan-wiki/#/dev/setup-local).

## Thank You

Your contributions make this project better for everyone. We appreciate your time and effort! If you have any questions, feel free to [open a new issue](https://github.com/tainacan/tainacan/issues/new) or start a discussion in [our users forum](https://tainacan.discourse.group/).
