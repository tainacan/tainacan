# W-582 — WYSIWYG toolbar ordering

## Acceptance proof

| Requirement | Production boundary | Automated proof | Browser proof |
| --- | --- | --- | --- |
| Undo and Redo are the final TinyMCE toolbar group | `src/views/admin/components/other/tainacan-wysiwyg.vue` | `tests/js/tainacan-wysiwyg-toolbar.test.mjs` | New-collection form using the rich-text description editor |

## TDD evidence

### RED

Command: `node tests/js/tainacan-wysiwyg-toolbar.test.mjs`

Observed result: failed as expected. The test reported the final group as `link` and expected `undo redo`.

### GREEN

Command: `node tests/js/tainacan-wysiwyg-toolbar.test.mjs`

Observed result: passed (1 test, 0 failures). The final toolbar group is `undo redo`, and no preceding group contains those controls.

### Build and runtime checks

- `npm run build`: passed. Webpack 5.110.1 compiled both configurations successfully.
- `curl --silent --show-error --output /dev/null --write-out 'local-admin-http=%{http_code}\\n' --max-time 10 http://tainacan-dev.localhost/wp-admin/`: returned `local-admin-http=302`, the expected redirect to local authentication.

## Browser-check record

Runner: Playwright with Chromium, viewport 1440×900. The supplied local account authenticated successfully at `http://tainacan-dev.localhost/wp-admin/`, then loaded the new-collection form at `http://tainacan-dev.localhost/wp-admin/admin.php?page=tainacan_admin#/collections/new`.

Visible toolbar order: Block Paragraph, Bold, Italic, Bullet list, Numbered list, Insert/edit link, Undo, Redo. The final two controls were asserted by their accessible labels. An unsaved editor update confirmed the form remained interactive and was not submitted. Screenshot: `.local-dev/w582-wysiwyg-toolbar.png`.

The local site was initially unavailable because its mounted `src` plugin lacked `src/vendor/autoload.php` and was inactive. Running `composer install` in this checkout restored the ignored dependency directory; activating the local `src` plugin restored the admin UI before this browser check.
