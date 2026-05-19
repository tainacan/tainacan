import axios from '../../../axios';

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
            reject(error.response.data);
        });
    });
};

/**
 * Dispatches `importer/fetchImporter`.
 * @returns {*} Action result.
 */
export const fetchImporter = ( { commit }, importerId ) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacanApi.get('importers/session/' +  importerId)
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject( error.response.data );
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
                reject( error.response.data );
            });
    });
};

/**
 * Dispatches `importer/updateImporter`.
 * @returns {*} Action result.
 */
export const updateImporter = ( { commit }, { sessionId, options }) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacanApi.put('importers/session/' + sessionId, options)
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error.response.data);
            });
    });
};

/**
 * Dispatches `importer/updateImporterCollection`.
 * @returns {*} Action result.
 */
export const updateImporterCollection = ( { commit }, { sessionId, collection }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacanApi.put('importers/session/' + sessionId, {
            collection: collection
        })
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error.response.data);
            });
    });
};

/**
 * Dispatches `importer/updateImporterURL`.
 * @returns {*} Action result.
 */
export const updateImporterURL = ( { commit }, { sessionId, url }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacanApi.put('importers/session/' + sessionId, {
            url: url
        })
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error.response.data);
            });
    });
};

/**
 * Dispatches `importer/updateImporterOptions`.
 * @returns {*} Action result.
 */
export const updateImporterOptions = ( { commit }, { sessionId, options }) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacanApi.put('importers/session/' + sessionId, {
            options
        })
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject(error.response.data);
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

        axios.tainacanApi.post('importers/session/' + sessionId + '/file', formData, {
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
                reject(error.response.data);
            });
    });
};

/**
 * Dispatches `importer/fetchImporterSourceInfo`.
 * @returns {*} Action result.
 */
export const fetchImporterSourceInfo = ({ commit }, sessionId ) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get('/importers/session/' + sessionId + '/source_info')
        .then((res) => {
            let importerSourceInfo = res.data;
            commit('setImporterSourceInfo', importerSourceInfo);
            resolve (importerSourceInfo);
        })
        .catch((error) => {
            reject(error.response.data);
        });
    });
};

/**
 * Dispatches `importer/runImporter`.
 * @returns {*} Action result.
 */
export const runImporter = ( { dispatch } , importerId ) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacanApi.post('importers/session/' + importerId + '/run')
            .then( res => {
                let backgroundProcessId = res.data;
                dispatch('bgprocess/fetchProcesses', { }, { root: true });
                resolve( backgroundProcessId );
            })
            .catch(error => {
                reject( error.response.data );
            });
    });
};

/**
 * Dispatches `importer/fetchMappingImporter`.
 * @returns {*} Action result.
 */
export const fetchMappingImporter = ( { commit }, { sessionId, collection } ) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacanApi.get('importers/session/' +  sessionId + '/get_mapping/' + collection)
            .then( res => {
                let mapping = res.data;
                commit('setMappingImporter', mapping);
                resolve( mapping );
            })
            .catch(error => {
                reject( error.response.data );
            });
    });
};
