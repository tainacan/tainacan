# Reloading the Item Editing Form

If your plugin live updates an item or its metadata from outside the Tainacan Admin Vue app — for example via the [REST API](https://redocly.github.io/redoc/?url=https://raw.githubusercontent.com/tainacan/tainacan/refs/heads/develop/docs/openapi.json ":ignore") or the [internal PHP API](/dev/internal-api.md) — the Item Editing Form will not notice by itself. Dispatch the `TainacanReloadItemMetadataForm` event on `window` so the form refetches and re-renders.

Do this instead of manipulating the form DOM. The listener is only active while the item edition screen is open.

## Reload a single metadatum

Pass both IDs in `event.detail`. The form fetches that metadatum and re-renders only its field.

`itemId` may be omitted; it then falls back to the item currently being edited. `metadatumId` is required for a partial reload. A non-empty `detail` that is missing `metadatumId` is ignored.

```js
window.dispatchEvent(
    new CustomEvent('TainacanReloadItemMetadataForm', {
        detail: {
            itemId: itemId,
            metadatumId: metadatumId
        }
    })
);
```

## Reload the whole form

Dispatch the event with no `detail`. The form shows its loading state and reloads every metadata field.

```js
window.dispatchEvent(
    new CustomEvent('TainacanReloadItemMetadataForm')
);
```

The value must already be persisted before you dispatch. This event only refreshes the UI from the server; it does not save data.

## See also

- [Plugin Development Guidelines](/dev/plugin-development-guidelines.md) — when to use this instead of other extension APIs
- [Using Admin Form Hooks](/dev/admin-form-hooks.md) — adding extra fields to the item form itself
