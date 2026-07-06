/**
 * Commits `exporter/setExporterSession` state changes.
 * @param {Object} state - Module state.
 * @param {*} exporterSession - Mutation payload.
 * @returns {void} No return value.
 */
export const setExporterSession = (state, exporterSession) => {
    state.exporterSession = exporterSession;
};

/**
 * Commits `exporter/setBackGroundProcessID` state changes.
 * @param {Object} state - Module state.
 * @param {*} backGroundProcessID - Mutation payload.
 * @returns {void} No return value.
 */
export const setBackGroundProcessID = (state, backGroundProcessID) => {
    state.setBackGroundProcessID = backGroundProcessID;
};
