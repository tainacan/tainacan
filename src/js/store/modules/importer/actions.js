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

export const updateImporterCollection = ( { commit }, { sessionId, collection }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.put('importers/session/' + sessionId, {
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

export const updateImporterURL = ( { commit }, { sessionId, url }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.put('importers/session/' + sessionId, {
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

export const updateImporterOptions = ( { commit }, { sessionId, options }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.put('importers/session/' + sessionId, {
            options: options
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

export const updateImporterFile = ( { commit }, { sessionId, file }) => {
    return new Promise(( resolve, reject ) => {
        
        let formData = new FormData();
        formData.append('file', file)

        axios.tainacan.post('importers/session/' + sessionId + '/file', formData, {
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

export const fetchImporterSourceInfo = ({ commit }, sessionId ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/importers/session/' + sessionId + '/source_info')
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

export const runImporter = ( { commit } , importerId ) => {
    return new Promise(( resolve, reject ) => {

        axios.tainacan.post('importers/session/' + importerId + '/run')
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
