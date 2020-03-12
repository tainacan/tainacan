import axios from '../../../axios';

export const fetchMetadata = ({commit}, {collectionId, isRepositoryLevel, isContextEdit, includeDisabled, isAdvancedSearch, parent}) => {

    const source = axios.CancelToken.source();

    return new Object({ 
        request: new Promise((resolve, reject) => {
            let endpoint = '';
            if (!isRepositoryLevel)
                endpoint = '/collection/' + collectionId + '/metadata/';
            else
                endpoint = '/metadata/';

            endpoint += '?nopaging=1';
            
            if (isContextEdit)
                endpoint += '&context=edit';

            if (includeDisabled)
                endpoint += '&include_disabled=' + includeDisabled;

            if (parent)
                endpoint += '&parent=' + parent;

            axios.tainacan.get(endpoint, { cancelToken: source.token })
                .then((res) => {
                    let metadata = res.data;
                    if (!isAdvancedSearch) {
                        if (parent && parent > 0)
                            commit('setChildrenMetadata', metadata);
                        else
                            commit('setMetadata', metadata);
                    }

                    resolve(metadata);
                })
                .catch((error) => {
                    if (axios.isCancel(error)) {
                        console.log('Request canceled: ', error.message);
                    } else {
                        reject(error);
                    }
                });
        }),
        source: source
    });
};

export const sendMetadatum = ({commit}, {collectionId, name, metadatumType, status, isRepositoryLevel, newIndex, parent}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';
        if (!isRepositoryLevel)
            endpoint = '/collection/' + collectionId + '/metadata/';
        else
            endpoint = '/metadata/';

        axios.tainacan.post(endpoint + '?context=edit', {
            name: name,
            metadata_type: metadatumType,
            status: status,
            parent: parent
        })
            .then(res => {
                let metadatum = res.data;
                if (metadatum.parent && metadatum.parent > 0)
                    commit('setSingleChildMetadatum', { metadatum: metadatum, index: newIndex });
                else
                    commit('setSingleMetadatum', { metadatum: metadatum, index: newIndex });
                resolve(metadatum);
            })
            .catch(error => {
                reject(error.response);
            });
    });
};

export const updateMetadatum = ({commit}, {collectionId, metadatumId, isRepositoryLevel, index, options}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';

        if (!isRepositoryLevel)
            endpoint = '/collection/' + collectionId + '/metadata/' + metadatumId;
        else
            endpoint = '/metadata/' + metadatumId;

        axios.tainacan.put(endpoint + '?context=edit', options)
            .then(res => {
                let metadatum = res.data;
                if (metadatum.parent && metadatum.parent > 0)
                    commit('setSingleChildMetadatum', { metadatum: metadatum, index: index });
                else
                    commit('setSingleMetadatum', { metadatum: metadatum, index: index });
                resolve(metadatum);
            })
            .catch(error => {
                reject({
                    error_message: error['response']['data'].error_message,
                    errors: error['response']['data'].errors
                });
            });
    });
};

export const fetchMetadatum = ({commit}, {collectionId, metadatumId}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';
        if (collectionId && collectionId != "default")
            endpoint = '/collection/' + collectionId + '/metadata/' + metadatumId;
        else
            endpoint = '/metadata/' + metadatumId;

        axios.tainacan.get(endpoint)
            .then((res) => {
                let metadata = res.data;
                resolve(metadata);
            })
            .catch((error) => {
                console.log(error);
                reject(error);
            });
    });
};

export const updateMetadata = ({commit}, metadata) => {
    commit('setMetadata', metadata);
};

export const updateChildrenMetadata = ({commit}, { metadata, parent }) => {
    commit('setChildrenMetadata', metadata, parent);
};

export const deleteMetadatum = ({commit}, {collectionId, metadatumId, isRepositoryLevel}) => {
    let endpoint = '';
    if (!isRepositoryLevel)
        endpoint = '/collection/' + collectionId + '/metadata/' + metadatumId;
    else
        endpoint = '/metadata/' + metadatumId;

    return new Promise((resolve, reject) => {
        axios.tainacan.delete(endpoint)
            .then(res => {
                const metadatum = res.data;
                if (metadatum.parent && metadatum.parent > 0)
                    commit('deleteChildrenMetadata', metadatum);
                else
                    commit('deleteMetadatum', metadatum);
                resolve(res.data);
            }).catch((error) => {
            console.log(error);
            reject(error);
        });

    });
};

export const cleanMetadata = ({commit}) => {
    commit('cleanMetadata');
};

export const cleanChildrenMetadata = ({commit}, parent) => {
    commit('cleanChildrenMetadata', parent );
};

export const updateCollectionMetadataOrder = ({ commit }, {collectionId, metadataOrder}) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/collections/' + collectionId + '/metadata_order?context=edit', {
            metadata_order: metadataOrder
        }).then(res => {
            commit('collection/setCollection', res.data, { root: true });
            commit('updateMetadataOrderFromCollection', res.data.metadata_order);
            resolve(res.data);
        }).catch(error => {
            reject(error.response);
        });

    });
}

export const fetchMetadatumTypes = ({commit}) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/metadata-types')
            .then((res) => {
                let metadatumTypes = res.data;
                commit('setMetadatumTypes', metadatumTypes);
                resolve(metadatumTypes);
            })
            .catch((error) => {
                console.log(error);
                reject(error);
            });
    });
}

export const updateMetadatumTypes = ({commit}, metadatumTypes) => {
    commit('setMetadatumTypes', metadatumTypes);
};

export const fetchMetadatumMappers = ({commit}) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/metadatum-mappers')
            .then((res) => {
                let metadatumMappers = res.data;
                commit('setMetadatumMappers', metadatumMappers);
                resolve(metadatumMappers);
            })
            .catch((error) => {
                console.log(error);
                reject(error);
            });
    });
}

export const updateMetadataMapperMetadata = ({ dispatch }, {metadataMapperMetadata, mapper}) => {
    return new Promise((resolve, reject) => {
        var param = {
                metadata_mappers: metadataMapperMetadata,
        };
        param[tainacan_plugin.exposer_mapper_param] = mapper;
        axios.tainacan.post('/metadatum-mappers', param).then((res) => {
                resolve(res.data);
            })
            .catch((error) => {
                console.log(error);
                reject(error);
            });
    });
}

export const updateMetadatumMappers = ({commit}, metadatumMappers) => {
    commit('setMetadatumMappers', metadatumMappers);
};


