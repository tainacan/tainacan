# W-582 — WYSIWYG toolbar ordering

## Acceptance proof

| Requirement | Production boundary | Automated proof | Browser proof |
| --- | --- | --- | --- |
| Undo and Redo are the final TinyMCE toolbar group | `src/views/admin/components/other/tainacan-wysiwyg.vue` | `tests/js/tainacan-wysiwyg-toolbar.test.mjs` | New-collection form using the rich-text description editor |
| Focused TinyMCE edit-area border uses Tainacan’s secondary color | `src/views/admin/components/other/tainacan-wysiwyg.vue` | `tests/js/tainacan-wysiwyg-toolbar.test.mjs` | New-collection form with the description editor focused |
| Focused TinyMCE container follows the standard input border-and-outline treatment | `src/views/admin/components/other/tainacan-wysiwyg.vue` | `tests/js/tainacan-wysiwyg-toolbar.test.mjs` | New-collection form with the description editor focused |

## TDD evidence

### RED

Command: `node tests/js/tainacan-wysiwyg-toolbar.test.mjs`

Observed result: failed as expected. The test reported the final group as `link` and expected `undo redo`.

### GREEN

Command: `node tests/js/tainacan-wysiwyg-toolbar.test.mjs`

Observed result: passed (1 test, 0 failures). The final toolbar group is `undo redo`, and no preceding group contains those controls.

### Toolbar spacing RED

Command: `npm run test:wysiwyg`

Observed result: failed as expected. The `uses header padding instead of toolbar group padding` assertion could not find the required scoped toolbar group rule.

### Toolbar spacing GREEN

Command: `npm run test:wysiwyg`

Observed result: passed (2 tests, 0 failures). The scoped rules remove toolbar group padding and set the editor header padding to `0 6px`.

### Focus color RED

Command: `npm run test:wysiwyg`

Observed result: failed as expected. The new focused edit-area assertion could not find a `.tox.tox-edit-focus .tox-edit-area::before` rule using `var(--tainacan-secondary)`.

### Focus color GREEN

Command: `npm run test:wysiwyg`

Observed result: passed (3 tests, 0 failures). The focused TinyMCE edit-area selector sets its pseudo-border to `var(--tainacan-secondary)`.

### Focus outline RED

Command: `npm run test:wysiwyg`

Observed result: failed as expected. The new assertion could not find the standard border and blended outline on `.tox.tox-edit-focus` outside the iframe-incompatible `:focus-within` state.

### Focus outline GREEN

Command: `npm run test:wysiwyg`

Observed result: passed (4 tests, 0 failures). The non-invalid TinyMCE focus class now receives the same secondary border, 2px outline, -1px offset, and blended outline color as standard Tainacan inputs.

### Build and runtime checks

- `npm run build`: passed. Webpack 5.110.1 compiled both configurations successfully.
- `curl --silent --show-error --output /dev/null --write-out 'local-admin-http=%{http_code}\\n' --max-time 10 http://tainacan-dev.localhost/wp-admin/`: returned `local-admin-http=302`, the expected redirect to local authentication.

## Browser-check record

Runner: Playwright with Chromium, viewport 1440×900. The supplied local account authenticated successfully at `http://tainacan-dev.localhost/wp-admin/`, then loaded the new-collection form at `http://tainacan-dev.localhost/wp-admin/admin.php?page=tainacan_admin#/collections/new`.

Visible toolbar order: Block Paragraph, Bold, Italic, Bullet list, Numbered list, Insert/edit link, Undo, Redo. The final two controls were asserted by their accessible labels. An unsaved editor update confirmed the form remained interactive and was not submitted. Screenshot: `.local-dev/w582-wysiwyg-toolbar.png`.

After the toolbar spacing change, the same Playwright check observed computed header padding of `0px 6px` and computed padding of `0px` for all five toolbar groups. The local `build.sh` workflow compiled and deployed the updated Vue bundle before this check.

For the focus-color check, the browser first recorded TinyMCE’s default focused pseudo-border as `rgb(0, 108, 231)`. After the focused TinyMCE selector was built and deployed, the editor had the `.tox-edit-focus` class and its `::before` border computed as `rgb(29, 57, 104)`, exactly matching the page’s resolved `--tainacan-secondary`. Screenshot: `.local-dev/w582-wysiwyg-focus.png`. The new-collection route deliberately overrides the secondary custom property to its repository-level accent; the check compares the resolved custom property, so it remains correct in other Tainacan color contexts.

`npm run build` emits the Vue bundle under the checkout’s `src/assets/js` directory, which is directly bind-mounted into the local WordPress container. The earlier stale browser result was not caused by a separate deployment directory.

For the standard focus-outline check, `npm run build` compiled successfully and the local WordPress container was restarted after its read-only bind mount retained a stale directory view. The mounted and HTTP-served Webpack runtime then referenced the current WYSIWYG chunk. In cache-disabled Chromium, the focused `.tox.tox-edit-focus` container computed a secondary border of `rgb(29, 57, 104)`, a 2px solid outline with -1px offset, and an outline color of `color(srgb 0.468235 0.534118 0.644706)`, matching the same `color-mix(...)` expression. Screenshot: `.local-dev/w582-wysiwyg-focus-outline.png`.

The local site was initially unavailable because its mounted `src` plugin lacked `src/vendor/autoload.php` and was inactive. Running `composer install` in this checkout restored the ignored dependency directory; activating the local `src` plugin restored the admin UI before this browser check.
