# Issue 1123 evidence

## Requirement

A native video slide without a descendant image must initialize without a JavaScript error and retain its accessible video label.

## Focused regression test

- Test: `tests-js/item-gallery-accessibility.test.cjs`
- Production boundary: `TainacanMediaGallery#enhanceLinksForAccessibility()`
- RED: `node tests-js/item-gallery-accessibility.test.cjs` failed before the production change with `TypeError: Cannot read properties of null (reading 'alt')` from `theme.js`.
- GREEN: `npm run test:js` passed after the null-safe image guard was added (1 test, 0 failures).

## Build and static checks

- `git diff --check` passed.
- `npm run build` passed. The development bundle emitted a new ignored item-gallery chunk containing the null-safe guard.

## Runtime verification limitation

No Docker Compose configuration is present in this checkout or its immediate plugin directory, so a local WordPress/Blocksy browser scenario could not be run here. The focused regression test executes the production gallery method with a native video slide; browser verification remains required before release.
