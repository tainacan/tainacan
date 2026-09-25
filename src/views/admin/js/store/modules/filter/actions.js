import axios from '../../../axios';
import qs from 'qs';

// FILTERS --------------------------------------------------------
/**
 * Dispatches `filter/fetchFilters`.
 * @returns {*} Action result.
 */
export const fetchFilters = ({ commit }, { collectionId, isRepositoryLevel, isContextEdit, includeDisabled, customFilters }) => {
    
    const source = axios.CancelToken.source();

    return new Object({ 
        request: new Promise((resolve, reject) => {
            
            let endpoint = '';
            if (!isRepositoryLevel) 
                endpoint = '/collection/' + collectionId + '/filters/';
            else
                endpoint = '/filters/';

            const query = {};

            if (isContextEdit)
                query.context = 'edit';

            if (includeDisabled)
                query.include_disabled = includeDisabled;

            if (customFilters != undefined && customFilters.length > 0)
                query.postin = customFilters;

            const queryString = qs.stringify(query);
            if (queryString)
                endpoint += '?' + queryString;

            axios.tainacanApi.get(endpoint, { cancelToken: source.token })
                .then((res) => {
                    let filters= res.data;
                    commit('setFilters', filters);
                    resolve (filters);
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
 * Dispatches `filter/sendFilter`.
 * @returns {*} Action result.
 */
export const sendFilter = ( { commit }, { collectionId, metadatumId, name, filterType, status, isRepositoryLevel, newIndex }) => {
    return new Promise(( resolve, reject ) => {
        let endpoint = '';
        if (!isRepositoryLevel) 
            endpoint = '/collection/' + collectionId + '/metadatum/' + metadatumId +'/filters/';
        else
            endpoint = '/filters/';

        axios.tainacanApi.post(endpoint + '?context=edit', {
            filter_type: filterType, 
            filter: {
                name: name,
                status: status
            },
            metadatum_id: metadatumId,
        })
            .then( res => {
                let filter = res.data;
                commit('addSingleFilter', { filter: filter , index: newIndex});
                resolve( filter );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

/**
 * Dispatches `filter/updateFilter`.
 * @returns {*} Action result.
 */
export const updateFilter = ( { commit }, { filterId, index, options }) => {

    if (options['metadatum'] != undefined && options['metadatum']['metadatum_id'] != undefined) {
        options['metadatum_id'] = options['metadatum']['metadatum_id'];
        delete options['metadatum'];
    }

    return new Promise(( resolve, reject ) => {
        let endpoint = '/filters/' + filterId;
        options['context'] = 'edit';

        axios.tainacanApi.put(endpoint, options)
            .then( res => {
                let filter = res.data;
                commit('setSingleFilter', { filter: filter, index: index });
                resolve( filter );
            })
            .catch( (error) => {
                console.log(JSON.parse(JSON.stringify(error)));
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

/**
 * Dispatches `filter/updateFilters`.
 * @returns {*} Action result.
 */
export const updateFilters = ( { commit }, filters) => {
    commit('setFilters', filters);
};

/**
 * Dispatches `filter/deleteFilter`.
 * @returns {*} Action result.
 */
export const deleteFilter = ({ commit }, filterId ) => {
    let endpoint = '/filters/' + filterId;

    return new Promise((resolve, reject) => {
        axios.tainacanApi.delete(endpoint, { data:{ is_permanently: false }})
        .then( res => {
            commit('deleteFilter', res.data );
            resolve( res.data );
        }).catch((error) => {
            reject( error );
        });

    }); 
};

/**
 * Dispatches `filter/deleteTemporaryFilter`.
 * @returns {*} Action result.
 */
export const deleteTemporaryFilter = ({ commit }, index ) => {
    commit('deleteTemporaryFilter', index );
};

/**
 * Dispatches `filter/addTemporaryFilter`.
 * @returns {*} Action result.
 */
export const addTemporaryFilter = ({ commit }, filter ) => {
    commit('addTemporaryFilter', filter );
};

/**
 * Dispatches `filter/updateCollectionFiltersOrder`.
 * @returns {*} Action result.
 */
export const updateCollectionFiltersOrder = ({ commit }, { collectionId, filtersOrder }) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.put('/collections/' + collectionId + '/filters_order?context=edit', {
            filters_order: filtersOrder
        }).then( res => {
            commit('collection/setCollection', res.data, { root: true });
            commit('updateFiltersOrderFromCollection', res.data.filters_order);
            resolve( res.data );
        }).catch( error => { 
            reject( error.response );
        });

    });
};

/**
 * Dispatches `filter/fetchFilterTypes`.
 * @returns {*} Action result.
 */
export const fetchFilterTypes = ({ commit} ) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get('/filter-types')
            .then((res) => {
                let filterTypes = res.data;
                commit('setFilterTypes', filterTypes);
                resolve (filterTypes);
            })
            .catch((error) => {
                console.log(error);
                reject(error);
            });
    });
};

/**
 * Dispatches `filter/updateFilteTypes`.
 * @returns {*} Action result.
 */
export const updateFilteTypes = ( { commit }, filterTypes) => {
    commit('setFilterTypes', filterTypes);
};

function groupFiltersByCollection(filters) {
    const grouped = {};

    if (!Array.isArray(filters))
        return grouped;

    for (const filter of filters) {
        const key = filter.collection_id == 'default'
            ? 'repository-filters'
            : String(filter.collection_name || filter.collection_id);

        if (!grouped[key])
            grouped[key] = [];

        grouped[key].push(filter);
    }

    return grouped;
}

// REPOSITORY COLLECTION FILTERS - MULTIPLE COLLECTIONS ------------------------
/**
 * Dispatches `filter/fetchRepositoryCollectionFilters`.
 * @returns {*} Action result.
 */
export const fetchRepositoryCollectionFilters = ({ commit } ) => {
    
    commit('clearRepositoryCollectionFilters');

    const source = axios.CancelToken.source();

    return Object({
        request: new Promise((resolve, reject) => {
            axios.tainacanApi.get('/filters/?include_control_metadata_types=true&include_disabled=false&append_from_collections=all', { cancelToken: source.token })
                .then((resp) => {
                    commit('setRepositoryCollectionFilters', groupFiltersByCollection(resp.data));
                    resolve();
                })
                .catch((error) => {
                    if (axios.isCancel(error))
                        console.log('Request canceled: ', error.message);
                    else
                        reject(error);
                });
        }),
        source: source
    });
};

// TAXONOMY FILTERS - MULTIPLE COLLECTIONS ------------------------
/**
 * Dispatches `filter/fetchTaxonomyFilters`.
 * @returns {*} Action result.
 */
export const fetchTaxonomyFilters = ({ dispatch, commit }, { taxonomyId, collectionsIds} ) => {
    
    commit('clearTaxonomyFilters');

    return new Promise((resolve, reject) => {

        dispatch('taxonomy/fetchTaxonomy', { taxonomyId: taxonomyId }, { root: true })
            .then((res) => {
                let taxonomy = res.taxonomy;
                if (taxonomy.collections_ids != undefined && taxonomy.collections_ids.length != undefined) {
                    const collectionsToSearch = collectionsIds.length ? collectionsIds : taxonomy.collections_ids;
                    const endpoint = '/filters/?include_control_metadata_types=true&include_disabled=false&append_from_collections=' + collectionsToSearch.join(',');

                    axios.tainacanApi.get(endpoint)
                        .then((resp) => {
                            const taxonomyFilters = (Array.isArray(resp.data) ? resp.data : []).filter((filter) => {
                                const filterTaxonomyId = filter
                                    && filter.metadatum
                                    && filter.metadatum.metadata_type_object
                                    && filter.metadatum.metadata_type_object.options
                                    && filter.metadatum.metadata_type_object.options.taxonomy_id;

                                return filterTaxonomyId != taxonomyId;
                            });

                            commit('setTaxonomyFilters', groupFiltersByCollection(taxonomyFilters));
                            resolve();
                        })
                        .catch((error) => {
                            console.log(error);
                            reject(error);
                        });
                }
            })
            .error(() => {
                reject();
            });
    });
};

/**
 * Dispatches `filter/moveFilterUp`.
 * @returns {*} Action result.
 */
export const moveFilterUp = ({ commit }, index) => {
    commit('moveFilterUp', index);
}

/**
 * Dispatches `filter/moveFilterDown`.
 * @returns {*} Action result.
 */
export const moveFilterDown = ({ commit }, index) => {
    commit('moveFilterDown', index);
}