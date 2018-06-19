import axios from '../../../axios/axios';

// IMPORTER ----------------------------------------------------
export const fetchAvailableImporters = ({ commit }) => {
    return new Promise((resolve, reject) => {

        axios.tainacan.get('importers/available')
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

export const fetchImporter = ( { commit } , { importerId }) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacan.post('importers/session/' +   importerId)
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject( error );
            });
    });
};

export const sendImporter = ( { commit }, importerTypeSlug) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacan.post('importers/session/', {
            importer_slug: importerTypeSlug
        })
            .then( res => {
                let importer = res.data;
                commit('setImporter', importer);
                resolve( importer );
            })
            .catch(error => {
                reject( error );
            });
    });
};

export const updateImporter = ( { commit }, { sessionId, options }) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacan.put('importers/session/' + sessionId, options)
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

export const updateImporterFile = ( { commit }, { sessionId, file }) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacan.put('importers/session/' + sessionId + '/file/' + file)
            .then( res => {
                let importerFile = res.data;
                commit('setImporterFile', importerFile);
                resolve( importerFile );
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const fetchImporterSourceInfo = ({ commit }, sessionId ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/importers/' + sessionId + 'sessionId')
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

export const runImporter = ( { commit } , { importerId }) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacan.post('importers/' + importerId + '/run')
            .then( res => {
                let backgroundProcessId = res.data;
                // probably send this a dedicated store to background process
                //commit('background/addBackgroundProcess', backgroundProcessId);
                resolve( backgroundProcessId );
            })
            .catch(error => {
                reject( error );
            });
    });
};
