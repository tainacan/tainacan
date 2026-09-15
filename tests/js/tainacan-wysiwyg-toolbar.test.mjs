import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

test('places undo and redo at the end of the WYSIWYG toolbar', async () => {
    const component = await readFile(
        new URL('../../src/views/admin/components/other/tainacan-wysiwyg.vue', import.meta.url),
        'utf8'
    );
    const toolbar = component.match(/toolbar:\s*'([^']+)'/)[1];
    const toolbarGroups = toolbar.split('|').map((group) => group.trim());

    assert.equal(toolbarGroups.at(-1), 'undo redo');
    assert.equal(toolbarGroups.slice(0, -1).includes('undo redo'), false);
});

test('uses header padding instead of toolbar group padding', async () => {
    const component = await readFile(
        new URL('../../src/views/admin/components/other/tainacan-wysiwyg.vue', import.meta.url),
        'utf8'
    );

    assert.match(component, /:deep\(\.tox \.tox-toolbar__group\)\s*\{\s*padding:\s*0;/);
    assert.match(component, /:deep\(\.tox:not\(\.tox-tinymce-inline\) \.tox-editor-header\)\s*\{\s*padding:\s*0 6px;/);
});

test('uses Tainacan secondary color for the focused edit-area border', async () => {
    const component = await readFile(
        new URL('../../src/views/admin/components/other/tainacan-wysiwyg.vue', import.meta.url),
        'utf8'
    );

    assert.match(
        component,
        /:deep\(\.tox\.tox-edit-focus \.tox-edit-area::before\)\s*\{\s*border-color:\s*var\(--tainacan-secondary\) !important;/
    );
});

test('uses the standard Tainacan focus border and outline for TinyMCE', async () => {
    const component = await readFile(
        new URL('../../src/views/admin/components/other/tainacan-wysiwyg.vue', import.meta.url),
        'utf8'
    );

    assert.match(
        component,
        /&:not\(\.is-invalid\)\s*\{\s*:deep\(\.tox\.tox-edit-focus\)\s*\{\s*border:\s*1px solid var\(--tainacan-secondary\) !important;\s*outline-width:\s*2px;\s*outline-offset:\s*-1px;\s*outline-color:\s*var\(--tainacan-secondary\);\s*outline-color:\s*color-mix\(in srgb, var\(--tainacan-secondary\) 60%, var\(--tainacan-background-color\)\);\s*outline-style:\s*solid;/
    );
});
