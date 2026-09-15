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
