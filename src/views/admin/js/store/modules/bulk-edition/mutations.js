/**
 * Commits `bulkedition/setGroup` state changes.
 * @param {Object} state - Module state.
 * @param {*} group - Mutation payload.
 * @returns {void} No return value.
 */
export const setGroup = (state, group) => {
    state.group = group;
};

/**
 * Commits `bulkedition/setItemIdInSequence` state changes.
 * @param {Object} state - Module state.
 * @param {*} itemIdInSequence - Mutation payload.
 * @returns {void} No return value.
 */
export const setItemIdInSequence = (state, itemIdInSequence) => {
    state.itemIdInSequence = itemIdInSequence;
};

/**
 * Commits `bulkedition/setLastUpdated` state changes.
 * @param {Object} state - Module state.
 * @param {*} value - Mutation payload.
 * @returns {void} No return value.
 */
export const setLastUpdated = (state, value) => {
    if (value != undefined)
        state.lastUpdated = value;
    else {
        let now = new Date();
        state.lastUpdated = now.toLocaleString();
    }
}
