/**
 * Reads derived state from `item/getItemMetadata`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getItemMetadata =  state => {
    return state.itemMetadata;
}

/**
 * Reads derived state from `item/getItem`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getItem = state => {
    return state.item;
}

/**
 * Reads derived state from `item/getLastUpdated`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getLastUpdated = state => {
    return state.lastUpdated;
}

/**
 * Reads derived state from `item/getItemTitle`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getItemTitle = state => {
    return state.itemTitle;
}

/**
 * Reads derived state from `item/getAttachments`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getAttachments =  state => {
    return state.attachments;
}

/**
 * Reads derived state from `item/getTotalAttachments`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTotalAttachments =  state => {
    return state.totalAttachments;
}

/**
 * Reads derived state from `item/getItemSubmission`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getItemSubmission =  state => {
    return state.itemSubmission;
}

/**
 * Reads derived state from `item/getItemSubmissionMetadata`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getItemSubmissionMetadata =  state => {
    return state.itemSubmissionMetadata;
}
