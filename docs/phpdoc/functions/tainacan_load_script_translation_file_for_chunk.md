# tainacan_load_script_translation_file_for_chunk


Filter callback for load_script_translation_file: resolves lazy-loaded chunk handles to the translation JSON.

Handles are aligned with build output (e.g. tainacan-chunks-blocks-{slug}-theme, tainacan-chunks-{name}-js-{name}-main),
so the pattern is always the handle. Finds the language-pack JSON whose comment.reference contains it.
Returns $file when no match (same as default WordPress behavior).

***

* Full name: `tainacan_load_script_translation_file_for_chunk`
* Defined in: `classes/tainacan-utils.php`

## Parameters

| Parameter | Type              | Description                           |
|-----------|-------------------|---------------------------------------|
| `$file`   | **string\|false** | Path to the translation file to load. |
| `$handle` | **string**        | Script handle.                        |
| `$domain` | **string**        | Text domain.                          |

## Return Value

**string|false**
