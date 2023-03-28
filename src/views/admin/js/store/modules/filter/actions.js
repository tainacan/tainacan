import axios from '../../../axios';
import qs from 'qs';

// FILTERS --------------------------------------------------------
export const fetchFilters = ({ commit }, { collectionId, isRepositoryLevel, isContextEdit, includeDisabled, customFilters }) => {
    
    const source = axios.CancelToken.source();

    return new Object({ 
        request: new Promise((resolve, reject) => {
            
            let endpoint = '';
            if (!isRepositoryLevel) 
                endpoint = '/collection/' + collectionId + '/filters/';
            else
                endpoint = '/filters/';

            endpoint += '?nopaging=1';

            if (isContextEdit) {
                endpoint += '&context=edit';
            }

            if (includeDisabled){
                endpoint += '&include_disabled=' + includeDisabled;
            }

            if (customFilters != undefined && customFilters.length > 0) {
                let postin = { 'postin': customFilters };
                endpoint += '&' + qs.stringify(postin);
            }

            axios.tainacan.get(endpoint, { cancelToken: source.token })
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

export const sendFilter = ( { commit }, { collectionId, metadatumId, name, filterType, status, isRepositoryLevel, newIndex }) => {
    return new Promise(( resolve, reject ) => {
        let endpoint = '';
        if (!isRepositoryLevel) 
            endpoint = '/collection/' + collectionId + '/metadatum/' + metadatumId +'/filters/';
        else
            endpoint = '/filters/';

        axios.tainacan.post(endpoint + '?context=edit', {
            filter_type: filterType, 
            filter: {
                name: name,
                status: status
            },
            metadatum_id: metadatumId,
        })
            .then( res => {
                let filter = res.data;
                commit('setSingleFilter', { filter: filter , index: newIndex});
                resolve( filter );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const updateFilter = ( { commit }, { filterId, index, options }) => {

    if (options['metadatum'] != undefined && options['metadatum']['metadatum_id'] != undefined) {
        options['metadatum_id'] = options['metadatum']['metadatum_id'];
        delete options['metadatum'];
    }

    return new Promise(( resolve, reject ) => {
        let endpoint = '/filters/' + filterId;
        options['context'] = 'edit';

        axios.tainacan.put(endpoint, options)
            .then( res => {
                let filter = res.data;
                commit('setSingleFilter', { filter: filter, index: index });
                resolve( filter );
            })
            .catch(error => {
                console.log(error);
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const updateFilters = ( { commit }, filters) => {
    commit('setFilters', filters);
};

export const deleteFilter = ({ commit }, filterId ) => {
    let endpoint = '/filters/' + filterId;

    return new Promise((resolve, reject) => {
        axios.tainacan.delete(endpoint, { data:{ is_permanently: false }})
        .then( res => {
            commit('deleteFilter', res.data );
            resolve( res.data );
        }).catch((error) => {
            reject( error );
        });

    }); 
};

export const deleteTemporaryFilter = ({ commit }, index ) => {
    commit('deleteTemporaryFilter', index );
};

export const addTemporaryFilter = ({ commit }, filter ) => {
    commit('addTemporaryFilter', filter );
};

export const updateCollectionFiltersOrder = ({ commit }, { collectionId, filtersOrder }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/collections/' + collectionId + '/filters_order?context=edit', {
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

export const fetchFilterTypes = ({ commit} ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/filter-types')
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

export const updateFilteTypes = ( { commit }, filterTypes) => {
    commit('setFilterTypes', filterTypes);
};

// REPOSITORY COLLECTION FILTERS - MULTIPLE COLLECTIONS ------------------------
export const fetchRepositoryCollectionFilters = ({ dispatch, commit } ) => {
    
    commit('clearRepositoryCollectionFilters');

    return new Promise((resolve, reject) => {

        dispatch('collection/fetchAllCollectionNames', { } ,{ root: true })
            .then((resp) => {
                resp.request
                    .then((res) => {
                        let collections = res;
                        if (collections != undefined && collections.length != undefined) {

                            let promises = [];

                            // First, we add repository level filters
                            promises.push(
                                axios.tainacan.get('/filters/?include_control_metadata_types=true&nopaging=1&include_disabled=false')
                                    .then((resp) => { return { filters: resp.data, collectionId: 'default' } }) 
                                    .catch((error) => {
                                        reject(error);
                                    })
                            );

                            // Then we add collection level filters
                            collections.forEach(collection => {
                                promises.push(
                                    axios.tainacan.get('/collection/' + collection.id + '/filters/?include_control_metadata_types=true&nopaging=1&include_disabled=false&metaquery[0][key]=collection_id&metaquery[0][value]=default&metaquery[0][compare]=!=')
                                        .then((resp) => { return { filters: resp.data, collectionId: collection.id } }) 
                                        .catch((error) => {
                                            reject(error);
                                        })
                                );
                            });
                            
                            // Process it all
                            axios.all(promises)
                                .then((results) => {
                                    let futureRepositoryCollectionFilters = {};
                                    
                                    for (let resp of results)
                                        futureRepositoryCollectionFilters[resp.collectionId != 'default' ? resp.collectionId : 'repository-filters'] = resp.filters;

                                    commit('setRepositoryCollectionFilters', futureRepositoryCollectionFilters);

                                    resolve();
                                })  
                                .catch((error) => {
                                    console.log(error);
                                    reject(error);
                                })   
                        }
                    })
                    .catch(() => {
                        reject();
                    });

                    // Search Request Token for cancelling
                    resolve(resp.source);
            });
    });
};

// TAXONOMY FILTERS - MULTIPLE COLLECTIONS ------------------------
export const fetchTaxonomyFilters = ({ dispatch, commit }, { taxonomyId, collectionsIds} ) => {
    
    commit('clearTaxonomyFilters');

    return new Promise((resolve, reject) => {

        dispatch('taxonomy/fetchTaxonomy', { taxonomyId: taxonomyId }, { root: true })
            .then((res) => {
                let taxonomy = res.taxonomy;
                if (taxonomy.collections_ids != undefined && taxonomy.collections_ids.length != undefined) {
                    
                    let promises = [];

                    // First, we add reporitory level search
                    promises.push(
                        axios.tainacan.get('/filters/?include_control_metadata_types=true&nopaging=1&include_disabled=false')
                            .then((resp) => { return { filters: resp.data, collectionId: 'default' } }) 
                            .catch((error) => {
                                reject(error);
                            })
                    );

                    // Then we add collection level filters
                    const collectionsToSearch = collectionsIds.length ? collectionsIds : taxonomy.collections_ids
                    collectionsToSearch.forEach(collectionId => {
                        promises.push(
                            axios.tainacan.get('/collection/' + collectionId + '/filters/?include_control_metadata_types=true&nopaging=1&include_disabled=false&metaquery[0][key]=collection_id&metaquery[0][value]=default&metaquery[0][compare]=!=')
                                .then((resp) => { return { filters: resp.data, collectionId: collectionId } }) 
                                .catch((error) => {
                                    reject(error);
                                })
                        );
                    });

                    // Process it all
                    axios.all(promises)
                        .then((results) => {
                            let futureTaxonomyFilters = {};

                            for (let resp of results) {
                                let taxonomyFilters = resp.filters.filter((filter) => {
                                    return filter.metadatum.metadata_type_object.options.taxonomy_id != taxonomyId
                                });
                                futureTaxonomyFilters[resp.collectionId != 'default' ? resp.collectionId : 'repository-filters'] = taxonomyFilters;
                            }

                            commit('setTaxonomyFilters', futureTaxonomyFilters);
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

export const moveFilterUp = ({ commit }, index) => {
    commit('moveFilterUp', index);
}

export const moveFilterDown = ({ commit }, index) => {
    commit('moveFilterDown', index);
}