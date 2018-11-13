import Vue from 'vue';

export const setAvailableImporters = (state, availableImporters) => {
    state.available_importers = availableImporters;
}

export const setImporter = (state, importer) => {
    state.importer = importer
}

export const setImporterFile = (state, importerFile) => {
    state.importer_file = importerFile;
}

export const setImporterSourceInfo= (state, importerSourceInfo) => {
    state.importer_source_info = importerSourceInfo;
}

export const setMappingImporter = (state, importerMapping) => {
    state.importer_mapping = importerMapping;
}