/**
 * Commits `activity/setActivities` state changes.
 * @param {Object} state - Module state.
 * @param {*} activities - Mutation payload.
 * @returns {void} No return value.
 */
export const setActivities = (state, activities) => {
  state.activities = activities;
};

/**
 * Commits `activity/setActivity` state changes.
 * @param {Object} state - Module state.
 * @param {*} activity - Mutation payload.
 * @returns {void} No return value.
 */
export const setActivity = (state, activity) => {
    state.activity = activity;
};

/**
 * Commits `activity/setActivityTitle` state changes.
 * @param {Object} state - Module state.
 * @param {*} eventTitle - Mutation payload.
 * @returns {void} No return value.
 */
export const setActivityTitle = (state, eventTitle) => {
    state.eventTitle = eventTitle;
};

/**
 * Commits `activity/clearActivity` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const clearActivity = (state) => {
  state.activity = {};
};
