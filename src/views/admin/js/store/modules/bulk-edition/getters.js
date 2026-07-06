/**
 * Reads derived state from `bulkedition/getGroupId`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getGroupId = state => {
    return state.group.id;
};

/**
 * Reads derived state from `bulkedition/getGroup`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getGroup = state => {
    return state.group;
};

/**
 * Reads derived state from `bulkedition/getItemIdInSequence`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getItemIdInSequence = state => {
    return state.itemIdInSequence;
};

/**
 * Reads derived state from `bulkedition/getLastUpdated`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getLastUpdated = state => {
    return state.lastUpdated;
}