/**
 * Reads derived state from `activity/getActivities`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getActivities = state => {
  return state.activities;
};

/**
 * Reads derived state from `activity/getActivity`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getActivity = state => {
    return state.activity;
};

/**
 * Reads derived state from `activity/getActivityTitle`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getActivityTitle = state => {
    return state.eventTitle;
};