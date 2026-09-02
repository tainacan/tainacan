/**
 * Broadcast selection state from the admin items UI to Selection Listeners.
 *
 * The Selection Listeners do not read the iframe's Location as we did previously (https://github.com/tainacan/tainacan/issues/1132).
 * The child publishes a plain snapshot of the selection state and search query, instead of the iframe's URL.
 *
 * Keep source/channel strings in sync with
 * gutenberg-blocks/js/selection/tainacan-selection.js.
 */
export const TAINACAN_SELECTION_SOURCE = 'tainacan-selection';
export const TAINACAN_SELECTION_CHANNEL = 'tainacan-selection';
export const INITIALLY_SELECTED_ITEMS_PARAM = 'initiallySelectedItems';

export function isIframeSelectionMode(app) {
    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        if (params.has('itemsSingleSelectionMode') || params.has('itemsMultipleSelectionMode') || params.has('itemsSearchSelectionMode'))
            return true;
    }

    const options = app && app.config.globalProperties.$adminOptions;
    return !!(options && (options.itemsSingleSelectionMode || options.itemsMultipleSelectionMode || options.itemsSearchSelectionMode));
}

export function toPlainItemIds(items) {
    if (items == null || items === false)
        return [];

    let list;
    try {
        list = typeof items === 'string' ? [items] : Array.from(items);
    } catch (error) {
        return [];
    }

    const seen = {};
    const result = [];
    for (let i = 0; i < list.length; i++) {
        const id = list[i] == null ? '' : String(list[i]);
        if (id === '' || id === 'false' || id === 'undefined' || id === 'null')
            continue;
        if (seen[id])
            continue;
        seen[id] = true;
        result.push(id);
    }
    return result;
}

/**
 * One-shot hydrate list from this iframe's location.search.
 * Gutenberg (and custom iframes) pass IDs here so checkboxes can restore
 * a previous selection. Not part of the Vue Router hash / postquery.
 */
export function getInitiallySelectedItemIds() {
    if (typeof window === 'undefined')
        return [];

    const raw = new URLSearchParams(window.location.search).get(INITIALLY_SELECTED_ITEMS_PARAM);
    if (!raw)
        return [];

    return toPlainItemIds(String(raw).split(/[,\s]+/)).map((id) => {
        return /^\d+$/.test(id) ? Number(id) : id;
    });
}

function cloneQuery(query) {
    try {
        return JSON.parse(JSON.stringify(query || {}));
    } catch (error) {
        return {};
    }
}

function openSelectionChannel() {
    try {
        if (typeof BroadcastChannel === 'undefined')
            return null;
        return new BroadcastChannel(TAINACAN_SELECTION_CHANNEL);
    } catch (error) {
        return null;
    }
}

let selectionChannel;

export function broadcastSelectionState(state) {
    const payload = {
        source: TAINACAN_SELECTION_SOURCE,
        selectedItems: toPlainItemIds(state && state.selectedItems),
        query: cloneQuery(state && state.query),
        href: state && state.href ? String(state.href) : (typeof window !== 'undefined' ? window.location.href : ''),
        collectionId: state && state.collectionId != null && state.collectionId !== '' ? String(state.collectionId) : ''
    };

    if (!selectionChannel)
        selectionChannel = openSelectionChannel();

    if (!selectionChannel)
        return;

    try {
        selectionChannel.postMessage(payload);
    } catch (error) {
        // Vue proxies should already have been cloned; ignore clone failures.
    }
}
