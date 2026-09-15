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
