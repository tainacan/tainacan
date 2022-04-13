import axios from '../../../axios';
import qs from 'qs';

export const fetchMetadata = ({commit}, {
    collectionId,
    isRepositoryLevel,
    isContextEdit,
    includeDisabled,
    isAdvancedSearch,
    parent,
    includeControlMetadataTypes,
    includeOptionsAsHtml,
    metaquery
}) => {

    const source = axios.CancelToken.source();

    return new Object({ 
        request: new Promise((resolve, reject) => {
            let endpoint = '';
            if (!isRepositoryLevel)
                endpoint = '/collection/' + collectionId + '/metadata/?';
            else
                endpoint = '/metadata/?';

            let query = {
                nopaging: 1
            };
            
            if (isContextEdit)
                query['context'] = 'edit';

            if (includeDisabled)
                query['include_disabled'] = includeDisabled;

            if (parent)
                query['parent'] = parent;
            
            if (includeControlMetadataTypes)
                query['include_control_metadata_types'] = 'true';

            if (includeOptionsAsHtml)
                query['include_options_as_html'] = 'yes';

            if (metaquery)
                query['metaquery'] = metaquery;

            axios.tainacan.get(endpoint + qs.stringify(query), { cancelToken: source.token })
                .then((res) => {
                    let metadata = res.data;
                    if (!isAdvancedSearch) {
                        if (parent == undefined || parent == null || parent <= 0 || parent == 'any')
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

export const sendMetadatum = ({commit}, {collectionId, name, metadatumType, status, isRepositoryLevel, newIndex, parent, includeOptionsAsHtml}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';
        if (!isRepositoryLevel)
            endpoint = '/collection/' + collectionId + '/metadata/';
        else
            endpoint = '/metadata/';

        endpoint += '?context=edit';

        if (includeOptionsAsHtml)
            endpoint += '&include_options_as_html=yes';

        axios.tainacan.post(endpoint, {
            name: name,
            metadata_type: metadatumType,
            status: status,
            parent: parent
        })
            .then(res => {
                let metadatum = res.data;
                commit('setSingleMetadatum', { metadatum: metadatum, index: newIndex, isRepositoryLevel: isRepositoryLevel });

                resolve(metadatum);
            })
            .catch(error => {
                reject(error.response);
            });
    });
};

export const updateMetadatum = ({commit}, {collectionId, metadatumId, isRepositoryLevel, index, options, includeOptionsAsHtml}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';

        if (!isRepositoryLevel)
            endpoint = '/collection/' + collectionId + '/metadata/' + metadatumId;
        else
            endpoint = '/metadata/' + metadatumId;

        endpoint += '?context=edit';

        if (includeOptionsAsHtml)
            endpoint += '&include_options_as_html=yes';

        axios.tainacan.put(endpoint, options)
            .then(res => {
                let metadatum = res.data;
                commit('setSingleMetadatum', { metadatum: metadatum, index: index, isRepositoryLevel: isRepositoryLevel });

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

export const updateChildMetadataOrder = ({ commit }, {isRepositoryLevel, collectionId, parentMetadatumId, childMetadataOrder }) => {
    let endpoint = '';
    
    if (isRepositoryLevel)
        endpoint = '/metadata/' + parentMetadatumId; 
    else
        endpoint = '/collection/' + collectionId + '/metadata/' + parentMetadatumId; 

    const body = {
        'metadata_type_options': {
            'children_order': childMetadataOrder
        }
    }

    return new Promise((resolve, reject) => {
        axios.tainacan.put(endpoint + '?context=edit', body)
            .then(res => {
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

// METADATA MAPPERS
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

// METADATA SECTIONS
export const fetchMetadataSections = ({commit}, {collectionId}) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/collection/' + collectionId + '/metadata-sections')
            .then((res) => {
                let metadataSections = res.data;
                commit('setMetadataSections', metadataSections);
                resolve(metadataSections);
            })
            .catch((error) => {
                reject(error);
            });
    });
}

export const sendMetadataSection = ({commit}, { collectionId, name, status, newIndex }) => {
    return new Promise((resolve, reject) => {
        let endpoint = '/collection/' + collectionId + '/metadata-sections/';

        endpoint += '?context=edit';

        axios.tainacan.post(endpoint, {
            name: name,
            status: status,
        })
            .then(res => {
                let metadataSection = res.data;
                commit('setSingleMetadataSection', { metadataSection: metadataSection, index: newIndex });

                resolve(metadataSection);
            })
            .catch(error => {
                reject(error.response);
            });
    });
};


export const updateMetadataSection = ({commit}, {collectionId, metadataSectionId, index, options }) => {
    return new Promise((resolve, reject) => {
        let endpoint = '/collection/' + collectionId + '/metadata/' + metadataSectionId;

        endpoint += '?context=edit';

        axios.tainacan.put(endpoint, options)
            .then(res => {
                let metadataSection = res.data;
                commit('setSingleMetadataSection', { metadataSection: metadataSection, index: index });
                resolve(metadataSection);
            })
            .catch(error => {
                reject({
                    error_message: error['response']['data'].error_message,
                    errors: error['response']['data'].errors
                });
            });
    });
};

export const deleteMetadataSection = ({commit}, { collectionId, metadataSectionId }) => {
    let endpoint = '/collection/' + collectionId + '/metadata-sections/' + metadataSectionId;

    return new Promise((resolve, reject) => {
        axios.tainacan.delete(endpoint)
            .then(res => {
                const metadataSection = res.data;
                commit('deleteMetadataSection', metadataSection);
                resolve(res.data);
            }).catch((error) => {
                console.log(error);
                reject(error);
            });
    });
};

export const updateMetadataSections = ({commit}, metadataSections) => {
    commit('setMetadataSections', metadataSections);
};

export const updateCollectionMetadataSectionsOrder = ({ commit }, {collectionId, metadataSectionsOrder}) => {

    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/collections/' + collectionId + '/metadata_sections_order?context=edit', {
            metadata_sections_order: metadataSectionsOrder
        }).then(res => {
            commit('collection/setCollection', res.data, { root: true });
            commit('updateMetadataSectionsOrderFromCollection', res.data.metadata_sections_order);
            resolve(res.data);
        }).catch(error => {
            reject(error.response);
        });
    });
}

export const cleanMetadataSections = ({commit}) => {
    commit('cleanMetadataSections');
};
