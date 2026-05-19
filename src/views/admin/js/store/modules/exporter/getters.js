/**
 * Reads derived state from `exporter/getExporterSession`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getExporterSession = state => {
    return state.exporterSession;
};

/**
 * Reads derived state from `exporter/getBGProccessID`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getBGProccessID = state => {
    return state.backGroundProcessID;
};