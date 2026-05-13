import axios from '../../../axios';
import qs from 'qs';

/**
 * Dispatches `metadata/fetchMetadata`.
 * @returns {*} Action result.
 */
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

            axios.tainacanApi.get(endpoint + qs.stringify(query), { cancelToken: source.token })
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

/**
 * Dispatches `metadata/sendMetadatum`.
 * @returns {*} Action result.
 */
export const sendMetadatum = ({commit}, {collectionId, name, metadatumType, status, isRepositoryLevel, newIndex, parent, includeOptionsAsHtml, sectionId }) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';
        if (!isRepositoryLevel)
            endpoint = '/collection/' + collectionId + '/metadata/';
        else
            endpoint = '/metadata/';

        endpoint += '?context=edit';

        if (includeOptionsAsHtml)
            endpoint += '&include_options_as_html=yes';

        let params = {
            name: name,
            metadata_type: metadatumType,
            status: status,
            parent: parent
        }

        if (sectionId != undefined && sectionId != false)
            params['metadata_section_id'] = sectionId;

        axios.tainacanApi.post(endpoint, params)
            .then(res => {
                let metadatum = res.data;
                
                if (sectionId != undefined && sectionId != false && !isRepositoryLevel)
                    commit('updateMetadatumInsideSectionMetadata', { metadatum: metadatum, index: newIndex, sectionId: sectionId })
                else
                    commit('setSingleMetadatum', { metadatum: metadatum, index: newIndex, isRepositoryLevel: isRepositoryLevel });

                resolve(metadatum);
            })
            .catch(error => {
                reject(error.response);
            });
    });
};

/**
 * Dispatches `metadata/updateMetadatum`.
 * @returns {*} Action result.
 */
export const updateMetadatum = ({commit}, {collectionId, metadatumId, isRepositoryLevel, index, options, includeOptionsAsHtml, sectionId }) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';

        if (!isRepositoryLevel) {
            endpoint = '/collection/' + collectionId + '/metadata/' + metadatumId;
        } else {
            endpoint = '/metadata/' + metadatumId;
            options['target_collection_id'] = collectionId;
        }

        endpoint += '?context=edit';

        if (includeOptionsAsHtml)
            endpoint += '&include_options_as_html=yes';

        if (sectionId != undefined)
            options['metadata_section_id'] = sectionId;

        axios.tainacanApi.put(endpoint, options)
            .then(res => {
                let metadatum = res.data;
                
                if (sectionId !== null && sectionId !== undefined && !isRepositoryLevel)
                    commit('updateMetadatumInsideSectionMetadata', { metadatum: metadatum, index: index, sectionId: sectionId });
                else
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

/**
 * Dispatches `metadata/fetchMetadatum`.
 * @returns {*} Action result.
 */
export const fetchMetadatum = ({commit}, {collectionId, metadatumId}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';
        if (collectionId && collectionId != "default")
            endpoint = '/collection/' + collectionId + '/metadata/' + metadatumId;
        else
            endpoint = '/metadata/' + metadatumId;

        axios.tainacanApi.get(endpoint)
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

/**
 * Dispatches `metadata/updateMetadata`.
 * @returns {*} Action result.
 */
export const updateMetadata = ({commit}, metadata) => {
    commit('setMetadata', metadata);
};

/**
 * Dispatches `metadata/deleteMetadatum`.
 * @returns {*} Action result.
 */
export const deleteMetadatum = ({commit}, {collectionId, metadatumId, isRepositoryLevel }) => {
    let endpoint = '';
    if (!isRepositoryLevel)
        endpoint = '/collection/' + collectionId + '/metadata/' + metadatumId;
    else
        endpoint = '/metadata/' + metadatumId;

    return new Promise((resolve, reject) => {
        axios.tainacanApi.delete(endpoint)
            .then(res => {
                const metadatum = res.data;
                if (metadatum.metadata_section_id && !isRepositoryLevel)
                    commit('deleteMetadatumInsideMetadataSection', metadatum)
                else
                    commit('deleteMetadatum', metadatum);
           
                resolve(res.data);
            })
            .catch((error) => {
                reject(error);
            });

    });
};

/**
 * Dispatches `metadata/cleanMetadata`.
 * @returns {*} Action result.
 */
export const cleanMetadata = ({commit}) => {
    commit('cleanMetadata');
};

/**
 * Dispatches `metadata/updateCollectionMetadataOrder`.
 * @returns {*} Action result.
 */
export const updateCollectionMetadataOrder = ({ commit }, { collectionId, metadataOrder, metadataSectionId }) => {

    return new Promise((resolve, reject) => {
        axios.tainacanApi.put('/collections/' + collectionId + '/metadata_section/' + metadataSectionId + '/metadata_order?context=edit', {
            metadata_order: metadataOrder
        }).then(res => {
            commit('collection/setCollection', res.data, { root: true });
            commit('updateCollectionMetadataOrder', { metadataOrder: res.data.metadata_order, metadataSectionId: metadataSectionId });
            resolve(res.data);
        }).catch(error => {
            reject(error.response);
        });

    });
}

/**
 * Dispatches `metadata/updateChildMetadataOrder`.
 * @returns {*} Action result.
 */
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
        axios.tainacanApi.put(endpoint + '?context=edit', body)
            .then(res => {
                resolve(res.data);
            }).catch(error => {
                reject(error.response);
            });
    });
}

/**
 * Dispatches `metadata/fetchMetadatumTypes`.
 * @returns {*} Action result.
 */
export const fetchMetadatumTypes = ({commit}) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get('/metadata-types')
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

/**
 * Dispatches `metadata/updateMetadatumTypes`.
 * @returns {*} Action result.
 */
export const updateMetadatumTypes = ({commit}, metadatumTypes) => {
    commit('setMetadatumTypes', metadatumTypes);
};

// METADATA MAPPERS
/**
 * Dispatches `metadata/fetchMetadatumMappers`.
 * @returns {*} Action result.
 */
export const fetchMetadatumMappers = ({commit}) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get('/metadatum-mappers')
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

/**
 * Dispatches `metadata/updateMetadataMapperMetadata`.
 * @returns {*} Action result.
 */
export const updateMetadataMapperMetadata = ({ dispatch }, {metadataMapperMetadata, mapper}) => {
    return new Promise((resolve, reject) => {
        var param = {
                metadata_mappers: metadataMapperMetadata,
        };
        param[tainacan_plugin.exposer_mapper_param] = mapper;
        axios.tainacanApi.post('/metadatum-mappers', param).then((res) => {
                resolve(res.data);
            })
            .catch((error) => {
                console.log(error);
                reject(error);
            });
    });
}

/**
 * Dispatches `metadata/updateMetadatumMappers`.
 * @returns {*} Action result.
 */
export const updateMetadatumMappers = ({commit}, metadatumMappers) => {
    commit('setMetadatumMappers', metadatumMappers);
};

// METADATA SECTIONS
/**
 * Dispatches `metadata/fetchMetadataSections`.
 * @returns {*} Action result.
 */
export const fetchMetadataSections = ({commit}, { collectionId, isContextEdit, includeDisabled }) => {

    let endpoint = '/collection/' + collectionId + '/metadata-sections';

    let params = {
        nopaging: 1
    };

    if (isContextEdit)
        params['context'] = 'edit';

    if (includeDisabled)
        params['include_disabled'] = true;

    return new Promise((resolve, reject) => {
        axios.tainacanApi.get(endpoint + '?' + qs.stringify(params) )
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

/**
 * Dispatches `metadata/sendMetadataSection`.
 * @returns {*} Action result.
 */
export const sendMetadataSection = ({commit}, { collectionId, name, status, newIndex }) => {
    return new Promise((resolve, reject) => {
        let endpoint = '/collection/' + collectionId + '/metadata-sections/';

        endpoint += '?context=edit';

        axios.tainacanApi.post(endpoint, {
            name: name,
            status: status,
        })
            .then(res => {
                let metadataSection = res.data;
                // commit('clearPlaceholderMetadataSection');
                commit('setSingleMetadataSection', { metadataSection: metadataSection, index: newIndex });
                
                resolve(res.data);
            })
            .catch(error => {
                reject(error.response);
            });
    });
};


/**
 * Dispatches `metadata/updateMetadataSection`.
 * @returns {*} Action result.
 */
export const updateMetadataSection = ({commit}, {collectionId, metadataSectionId, index, options }) => {
    return new Promise((resolve, reject) => {
        let endpoint = '/collection/' + collectionId + '/metadata-sections/' + metadataSectionId;

        endpoint += '?context=edit';

        axios.tainacanApi.put(endpoint, options)
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

/**
 * Dispatches `metadata/deleteMetadataSection`.
 * @returns {*} Action result.
 */
export const deleteMetadataSection = ({commit}, { collectionId, metadataSectionId }) => {
    let endpoint = '/collection/' + collectionId + '/metadata-sections/' + metadataSectionId;

    return new Promise((resolve, reject) => {
        axios.tainacanApi.delete(endpoint)
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

/**
 * Dispatches `metadata/updateMetadataSections`.
 * @returns {*} Action result.
 */
export const updateMetadataSections = ({commit}, metadataSections) => {
    commit('setMetadataSections', metadataSections);
};

/**
 * Dispatches `metadata/updateCollectionMetadataSectionsOrder`.
 * @returns {*} Action result.
 */
export const updateCollectionMetadataSectionsOrder = ({ commit }, {collectionId, metadataSectionsOrder }) => {

    return new Promise((resolve, reject) => {
        axios.tainacanApi.put('/collections/' + collectionId + '/metadata_section_order?context=edit', {
            metadata_section_order: metadataSectionsOrder
        }).then(res => {
            commit('collection/setCollection', res.data, { root: true });
            commit('updateMetadataSectionsOrderFromCollection', res.data.metadata_section_order);
            resolve(res.data);
        }).catch(error => {
            reject(error.response);
        });
    });
}

/**
 * Dispatches `metadata/cleanMetadataSections`.
 * @returns {*} Action result.
 */
export const cleanMetadataSections = ({commit}) => {
    commit('cleanMetadataSections');
};

/**
 * Dispatches `metadata/moveMetadataSectionUp`.
 * @returns {*} Action result.
 */
export const moveMetadataSectionUp = ({ commit }, index) => {
    commit('moveMetadataSectionUp', index);
}

/**
 * Dispatches `metadata/moveMetadataSectionDown`.
 * @returns {*} Action result.
 */
export const moveMetadataSectionDown = ({ commit }, index) => {
    commit('moveMetadataSectionDown', index);
}

/**
 * Dispatches `metadata/moveMetadatumUp`.
 * @returns {*} Action result.
 */
export const moveMetadatumUp = ({ commit }, { index, sectionIndex }) => {
    commit('moveMetadatumUp', { index, sectionIndex });
}

/**
 * Dispatches `metadata/moveMetadatumDown`.
 * @returns {*} Action result.
 */
export const moveMetadatumDown = ({ commit }, { index, sectionIndex }) => {
    commit('moveMetadatumDown', { index, sectionIndex });
}

/**
 * Dispatches `metadata/moveMetadatum`.
 * @returns {*} Action result.
 */
export const moveMetadatum = ({ commit }, { newIndex, oldIndex, sectionIndex }) => {
    commit('moveMetadatum', { newIndex, oldIndex, sectionIndex });
}

// METADATA SECTION METADATA LIST
/**
 * Dispatches `metadata/fetchMetadataSectionMetadata`.
 * @returns {*} Action result.
 */
export const fetchMetadataSectionMetadata = ({commit}, { collectionId , metadataSectionId }) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get('/collection/' + collectionId + '/metadata-sections/' + metadataSectionId + '/metadata')
            .then((res) => {
                let metadataSectionMetadata = res.data;
                commit('setMetadataSectionMetadata', { metadataSectionId: metadataSectionId, metadata: metadataSectionMetadata });
                resolve(metadataSectionMetadata);
            })
            .catch((error) => {
                reject(error);
            });
    });
}

