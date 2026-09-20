/**
 * Gutenberg side of iframe item selection: subscribe to admin snapshots and
 * pass a previous selection into the iframe URL.
 *
 * Keep source, channel, and initiallySelectedItems in sync with
 * admin/js/selection-bridge.js.
 */
export const TAINACAN_SELECTION_SOURCE = 'tainacan-selection';
export const TAINACAN_SELECTION_CHANNEL = 'tainacan-selection';
export const INITIALLY_SELECTED_ITEMS_PARAM = 'initiallySelectedItems';

function normalizeSelectionState(data) {
    if (!data || data.source !== TAINACAN_SELECTION_SOURCE)
        return null;

    return {
        selectedItems: Array.isArray(data.selectedItems) ? data.selectedItems.map(String) : [],
        query: data.query && typeof data.query === 'object' ? data.query : {},
        href: data.href ? String(data.href) : '',
        collectionId: data.collectionId != null ? String(data.collectionId) : ''
    };
}

export function subscribeSelectionState(onState) {
    let channel = null;
    try {
        if (typeof BroadcastChannel !== 'undefined') {
            channel = new BroadcastChannel(TAINACAN_SELECTION_CHANNEL);
            channel.onmessage = (event) => {
                const state = normalizeSelectionState(event.data);
                if (state)
                    onState(state);
            };
        }
    } catch (error) {
        channel = null;
    }

    return () => {
        if (channel)
            channel.close();
    };
}

function toInitiallySelectedIds(itemIds) {
    const list = itemIds == null || itemIds === false
        ? []
        : (Array.isArray(itemIds) ? itemIds : [itemIds]);

    const seen = {};
    const result = [];
    for (let i = 0; i < list.length; i++) {
        const id = list[i] == null ? '' : String(list[i]);
        if (id === '' || id === 'false' || id === 'undefined' || id === 'null')
            continue;
        const numeric = Number(id);
        if (!Number.isFinite(numeric) || numeric <= 0)
            continue;
        if (seen[id])
            continue;
        seen[id] = true;
        result.push(id);
    }
    return result;
}

/**
 * Put initiallySelectedItems on location.search (not the Vue Router hash).
 */
export function appendInitiallySelectedItemsParam(url, itemIds) {
    if (!url)
        return url;

    const ids = toInitiallySelectedIds(itemIds);
    if (!ids.length)
        return url;

    const hashIndex = url.indexOf('#');
    const beforeHash = hashIndex >= 0 ? url.slice(0, hashIndex) : url;
    const hash = hashIndex >= 0 ? url.slice(hashIndex) : '';
    if (beforeHash.indexOf(INITIALLY_SELECTED_ITEMS_PARAM + '=') >= 0)
        return url;

    const separator = beforeHash.indexOf('?') >= 0 ? '&' : '?';
    return beforeHash + separator + INITIALLY_SELECTED_ITEMS_PARAM + '=' + encodeURIComponent(ids.join(',')) + hash;
}
