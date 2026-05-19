/**
 * Reads derived state from `exposer/getAvailableExposers`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getAvailableExposers = state => {
    return state.availableExposers;
};
