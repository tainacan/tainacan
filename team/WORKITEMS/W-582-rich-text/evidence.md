# W-582 Rich Text metadata evidence

## Slice 1: built-in type registration

- Requirement: `Rich Text` appears in the metadata type registry as a non-core long-string field.
- Test: `tests/test-rich-text-metadata.php::test_rich_text_is_a_registered_non_core_metadata_type`.
- RED, 2026-10-01: `.local-dev/run-phpunit.sh --filter Rich_Text_Metadata` reached PHPUnit and failed as expected: `Failed asserting that an array contains 'Tainacan\\Metadata_Types\\Rich_Text'` (1 test, 1 assertion, 1 failure). The earlier direct-file invocation failed test discovery; the initial sandboxed invocation failed database connection. Neither is counted as RED.
- GREEN, 2026-10-01: the same filtered command passed after registering the new PHP type (1 test, 4 assertions).

## Slice 2: REST HTML round trip and sanitization

- Requirement: the item metadata REST route keeps safe rich HTML, including links, and returns display HTML without plain-text conversion; executable markup is removed. This remains true with the global editor option disabled.
- Boundary: `PUT` and `GET /tainacan/v2/item/{item_id}/metadata/{metadatum_id}`, dispatched through the WordPress REST server.
- Tests: `test_rest_round_trip_keeps_safe_html_without_converting_it_again`, `test_rest_write_removes_executable_markup_but_preserves_safe_links`.
- RED, 2026-10-01: `.local-dev/run-phpunit.sh --filter Rich_Text_Metadata` reported two expected failures: safe `<a>` tags were removed from stored values; 3 tests, 11 assertions, 2 failures.
- GREEN, 2026-10-01: after selecting rich-text sanitization for `Rich_Text` and providing its own HTML display method, the focused run passed: 3 tests, 15 assertions.

## Slice 3: visually empty editor value

- Requirement: TinyMCE's empty paragraph does not satisfy a required Rich Text metadatum and clears an optional one.
- Boundary: item metadata REST PUT, `Item_Metadata_Entity::set_value`, validation, and post-meta persistence.
- RED, 2026-10-01: focused run had 2 expected failures: required `<p><br></p>` returned 200, and optional value left a post-meta row (6 tests, 22 assertions, 2 failures).
- GREEN, 2026-10-01: after Rich Text-specific normalization before validation, focused run passed (6 tests, 23 assertions).
- The newly added multiple-list assertion passed on its first run. It is a coverage-gap test, not RED evidence; a sensitivity probe remains due.

## Existing registry count

- RED, 2026-10-01: `.local-dev/run-phpunit.sh --filter test_metadata_metadata_type` failed as expected, reporting 12 types against the previous assertion of 11. Updated the assertion to 12.

## Slice 4: browser component and last-value delivery

- Requirement: Rich Text uses the editor with the global option off, while ordinary Textarea stays plain; input reaches the parent before blur and focus/blur events are propagated.
- Browser runner: `.local-dev/tests/js/rich-text-metadata-component.test.mjs`, 1440×900, `about:blank` component harness loading the actual Vue single-file component source with Vue's browser compiler. The TinyMCE wrapper is represented by a test input; this verifies integration of the field component, not TinyMCE itself.
- RED, 2026-10-01: the first browser run reached the assertion but the parent still held the old value. Diagnostic page error was `this.emitValue.flush is not a function`; Vue method binding removed Lodash debounce's `flush` property. The sandboxed browser launch had failed before execution and is not RED evidence.
- GREEN, 2026-10-01: replaced the bound Lodash method with a component-owned timer and explicit `flushValue()`. The browser run passed with global setting false, rich input present, plain Textarea present, updated HTML value, focus/blur events and no page errors.

## Regression and documentation gates

- `.local-dev/run-phpunit.sh` passed: 358 tests, 3967 assertions (2m30s). Existing PHP deprecation notices appeared.
- `npm run build` passed after Vue and display changes; webpack reported warnings but no errors.
- `php -l` passed on the new type class and the two modified PHP persistence files; `git diff --check` passed.
- `./generate_docs.sh` completed successfully. The generator changed 102 previously tracked generated files, mostly unrelated order/format drift. Those generated tracked files were restored from HEAD after verifying they had no preexisting changes; the new `Rich_Text.md` and `Rich_Text.mmd` class documents remain. `docs/rich-text-editor.md` was updated manually as the human-facing document.
- The ignored local TinyMCE toolbar test has 9 passes and 1 preexisting failure: it expects `plugins: 'link lists code'`, while the shared editor already uses `plugins: 'link lists code autolink'`; this feature does not change that component.

## Coverage-gap sensitivity probe

- The multiple-list test passed on its first execution. Saved `class-tainacan-rich-text.php` outside the repository, changed only the generated list opening tag from `<ul>` to `<ol>`, and ran the full relevant API group (`.local-dev/run-phpunit.sh --group api`). It failed only `test_multiple_values_render_as_html_without_plain_text_conversion`: 175 tests, 953 assertions, 1 failure.
- Restored the original file by copying it back; byte comparison with the backup passed. The same full API group then passed: 175 tests, 953 assertions. The temporary mutation is absent from the working tree.
- `test_rich_text_definition_can_be_created_through_rest_api` also passed on first execution. For its sensitivity probe, temporarily changed only the PHP type component from `tainacan-rich-text` to `tainacan-textarea` and ran the full API group. The registry and REST definition tests both failed (175 tests, 952 assertions, 2 failures). Restored the backed-up class and reran the full API group successfully (175 tests, 953 assertions).
- The browser assertion that Rich Text remains an editor with the global setting off also passed on first execution. Saved `TainacanRichText.vue` outside the repository, temporarily rendered `b-input` in place of `tainacan-rich-text-editor`, and ran the complete component browser test. It failed on `plain !== rich`. Restored the original by copying it back, confirmed byte equality, and reran the browser test successfully with no page errors.

## Live admin E2E

- Tried `.local-dev/rich-text-metadata-e2e.mjs` at 1440×900. Its first test site, `centrodememoria.localhost`, mounts a different plugin checkout: the created type fell back to Text in the item form. Both disposable fixtures were moved to Trash. This is an environment mismatch, not a failure of this checkout.
- Confirmed that `tainacan-dev.localhost` mounts this checkout. An initial attempt there could not authenticate with the old test credentials; no fixture was created. After the credentials were updated, the script authenticated and reached the real item form.
- The first authenticated run stopped on an invalid test assumption that the TinyMCE body would not have focus immediately after page load. The test-only assertion was removed. No product code changed for this failure.
- With the global editor option enabled (`1`), the real admin E2E passed at 1440×900: it created a Rich Text definition and item, mounted TinyMCE, entered text, saved `<p>Saved with Rich Text</p>` through the item metadata route, reloaded the form, found the same content, and observed no page errors.
- The site's original `tainacan_option_allow_rich_text_editor` was `1`, with no override constant. Temporarily set it to `0`, reran the same E2E, and asserted the localized setting was falsy. The test passed: Rich Text still mounted TinyMCE, saved and reloaded the HTML, and produced no page errors. A shell exit trap restored the option to `1`; a read-only WP-CLI check confirmed the restored value.
- All disposable collections and items from these tests were moved to Trash by the E2E script.
- The automatic approval review rejected the original E2E script because it permanently deleted test fixtures. The script was changed to move its item and collection fixtures to Trash instead; the reversible version was approved and run.
- A read-only follow-up query confirmed both `Rich Text E2E` collections from the mismatched site are in Trash (IDs 92839 and 92853); none remained published or draft.
