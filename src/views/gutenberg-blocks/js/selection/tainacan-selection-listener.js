/**
 * Receive selection snapshots published by the admin items iframe.
 *
 * Keep source/channel strings in sync with admin/js/selection-bridge.js.
 */
export const TAINACAN_SELECTION_SOURCE = 'tainacan-selection';
export const TAINACAN_SELECTION_CHANNEL = 'tainacan-selection';

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
