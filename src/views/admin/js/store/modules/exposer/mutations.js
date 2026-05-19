/**
 * Commits `exposer/setAvailableExposers` state changes.
 * @param {Object} state - Module state.
 * @param {*} availableExposers - Mutation payload.
 * @returns {void} No return value.
 */
export const setAvailableExposers = (state, availableExposers) => {
    state.availableExposers = availableExposers;
};
