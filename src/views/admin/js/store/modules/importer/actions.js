import axios from '../../../axios/axios';

/**
 * Builds a REST path from a template. Each inserted value must be hexadecimal,
 * the same shape the importer session routes accept. The value is encoded
 * before it is joined to the path. Returns null when one is not.
 * @param {TemplateStringsArray} strings
 * @param {...unknown} values
 * @returns {string|null}
 */
function hexPath(strings, ...values) {
    let path = strings[0];

    for (let index = 0; index < values.length; index++) {
        const id = String(values[index] == null ? '' : values[index]);
        if (!/^[0-9a-f]+$/.test(id))
            return null;
        path += encodeURIComponent(id) + strings[index + 1];
    }

    return path;
}

// IMPORTER ----------------------------------------------------
/**
 * Dispatches `importer/fetchAvailableImporters`.
 * @returns {*} Action result.
 */
export const fetchAvailableImporters = ({ commit }) => {
    return new Promise((resolve, reject) => {

        axios.tainacanApi.get('importers/available')
        .then((res) => {
            let availableImporters = res.data;
            commit('setAvailableImporters', availableImporters);
            resolve (availableImporters);
        }) 
        .catch((error) => {
            reject(error);
        });
    });
};

/**
 * Dispatches `importer/fetchImporter`.
 * @returns {*} Action result.
 */
export const fetchImporter = ( { commit }, importerId ) => {
    return new Promise(( resolve, reject ) => {

        const endpoint = hexPath`importers/session/${importerId}`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        axios.tainacanApi.get(endpoint)
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `importer/sendImporter`.
 * @returns {*} Action result.
 */
export const sendImporter = ( { commit }, importerTypeSlug) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacanApi.post('importers/session/', {
            importer_slug: importerTypeSlug
        })
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `importer/updateImporter`.
 * @returns {*} Action result.
 */
export const updateImporter = ( { commit }, { sessionId, options }) => {
    return new Promise(( resolve, reject ) => {

        const endpoint = hexPath`importers/session/${sessionId}`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        axios.tainacanApi.put(endpoint, options)
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `importer/updateImporterCollection`.
 * @returns {*} Action result.
 */
export const updateImporterCollection = ( { commit }, { sessionId, collection }) => {
    return new Promise(( resolve, reject ) => {
        const endpoint = hexPath`importers/session/${sessionId}`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        axios.tainacanApi.put(endpoint, {
            collection: collection
        })
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `importer/updateImporterURL`.
 * @returns {*} Action result.
 */
export const updateImporterURL = ( { commit }, { sessionId, url }) => {
    return new Promise(( resolve, reject ) => {
        const endpoint = hexPath`importers/session/${sessionId}`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        axios.tainacanApi.put(endpoint, {
            url: url
        })
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `importer/updateImporterOptions`.
 * @returns {*} Action result.
 */
export const updateImporterOptions = ( { commit }, { sessionId, options }) => {
    return new Promise(( resolve, reject ) => {

        const endpoint = hexPath`importers/session/${sessionId}`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        axios.tainacanApi.put(endpoint, {
            options
        })
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `importer/updateImporterFile`.
 * @returns {*} Action result.
 */
export const updateImporterFile = ( { commit }, { sessionId, file }) => {
    return new Promise(( resolve, reject ) => {
        
        let formData = new FormData();
        formData.append('file', file)

        const endpoint = hexPath`importers/session/${sessionId}/file`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        axios.tainacanApi.post(endpoint, formData, {
                headers: { 
                    'Content-Type': 'multipart/form-data;', 
                    'Content-Disposition': 'form-data; filename=' + file.name + '; filesize=' + file.size}, 
            })
            .then( res => {
                let updatedImporter = res.data;
                commit('setImporter', updatedImporter);
                resolve( updatedImporter );
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `importer/fetchImporterSourceInfo`.
 * @returns {*} Action result.
 */
export const fetchImporterSourceInfo = ({ commit }, sessionId ) => {
    return new Promise((resolve, reject) => {
        const endpoint = hexPath`/importers/session/${sessionId}/source_info`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        axios.tainacanApi.get(endpoint)
        .then((res) => {
            let importerSourceInfo = res.data;
            commit('setImporterSourceInfo', importerSourceInfo);
            resolve (importerSourceInfo);
        })
        .catch((error) => {
            reject(error);
        });
    });
};

/**
 * Dispatches `importer/runImporter`.
 * @returns {*} Action result.
 */
export const runImporter = ( { dispatch } , importerId ) => {
    return new Promise(( resolve, reject ) => {

        const endpoint = hexPath`importers/session/${importerId}/run`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        axios.tainacanApi.post(endpoint)
            .then( res => {
                let backgroundProcessId = res.data;
                dispatch('bgprocess/fetchProcesses', { }, { root: true });
                resolve( backgroundProcessId );
            })
            .catch(error => {
                reject(error);
            });
    });
};

/**
 * Dispatches `importer/fetchMappingImporter`.
 * @returns {*} Action result.
 */
export const fetchMappingImporter = ( { commit }, { sessionId, collection } ) => {
    return new Promise(( resolve, reject ) => {

        const endpoint = hexPath`importers/session/${sessionId}/get_mapping/${collection}`;
        if (endpoint == null) {
            reject(new Error('Invalid session id.'));
            return;
        }

        axios.tainacanApi.get(endpoint)
            .then( res => {
                let mapping = res.data;
                commit('setMappingImporter', mapping);
                resolve( mapping );
            })
            .catch(error => {
                reject(error);
            });
    });
};
