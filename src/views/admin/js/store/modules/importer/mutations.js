

/**
 * Commits `importer/setAvailableImporters` state changes.
 * @param {Object} state - Module state.
 * @param {*} availableImporters - Mutation payload.
 * @returns {void} No return value.
 */
export const setAvailableImporters = (state, availableImporters) => {
    state.available_importers = availableImporters;
}

/**
 * Commits `importer/setImporter` state changes.
 * @param {Object} state - Module state.
 * @param {*} importer - Mutation payload.
 * @returns {void} No return value.
 */
export const setImporter = (state, importer) => {
    state.importer = importer
}

/**
 * Commits `importer/setImporterFile` state changes.
 * @param {Object} state - Module state.
 * @param {*} importerFile - Mutation payload.
 * @returns {void} No return value.
 */
export const setImporterFile = (state, importerFile) => {
    state.importer_file = importerFile;
}

/**
 * Commits `importer/setImporterSourceInfo` state changes.
 * @param {Object} state - Module state.
 * @param {*} importerSourceInfo - Mutation payload.
 * @returns {void} No return value.
 */
export const setImporterSourceInfo= (state, importerSourceInfo) => {
    state.importer_source_info = importerSourceInfo;
}

/**
 * Commits `importer/setMappingImporter` state changes.
 * @param {Object} state - Module state.
 * @param {*} importerMapping - Mutation payload.
 * @returns {void} No return value.
 */
export const setMappingImporter = (state, importerMapping) => {
    state.importer_mapping = importerMapping;
}
