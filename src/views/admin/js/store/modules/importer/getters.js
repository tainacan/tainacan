/**
 * Reads derived state from `importer/getgetAvailableImporters`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getgetAvailableImporters = state => {
    return state.available_importers;
}

/**
 * Reads derived state from `importer/getImporter`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getImporter = state => {
    return state.importer
}

/**
 * Reads derived state from `importer/getImporterSourceInfo`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getImporterSourceInfo = state => {
    return state.importer_source_info;
}

/**
 * Reads derived state from `importer/getImporterFile`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getImporterFile = state => {
    return state.importer_file;
}

/**
 * Reads derived state from `importer/getImporterMapping`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getImporterMapping = state => {
    return state.importer_mapping
}