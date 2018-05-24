import axios from '../../../axios/axios';

// FILTERS --------------------------------------------------------
export const fetchFilters = ({ commit }, {collectionId, isRepositoryLevel, isContextEdit, includeDisabled}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';
        if (!isRepositoryLevel) 
            endpoint = '/collection/' + collectionId + '/filters/';
        else
            endpoint = '/filters/';

        endpoint += '?nopaging=1'

        if (isContextEdit) {
            endpoint += '&context=edit';
        }

        if (includeDisabled === 'yes'){
            endpoint += '&include_disabled=yes'
        }

        axios.tainacan.get(endpoint)
        .then((res) => {
            let filters= res.data;
            commit('setFilters', filters);
            resolve (filters);
        }) 
        .catch((error) => {
            console.log(error);
            reject(error);
        });
    });
};

export const sendFilter = ( { commit }, { collectionId, fieldId, name, filterType, status, isRepositoryLevel, newIndex }) => {
    return new Promise(( resolve, reject ) => {
        let endpoint = '';
        if (!isRepositoryLevel) 
            endpoint = '/collection/' + collectionId + '/field/' + fieldId +'/filters/'; 
        else
            endpoint = '/filters/';
        axios.tainacan.post(endpoint + '?context=edit', {
            filter_type: filterType, 
            filter: {
                name: name,
                status: status
            }
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
            console.log(error);
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
        axios.tainacan.patch('/collections/' + collectionId, {
            filters_order: filtersOrder
        }).then( res => {
            commit('collection/setCollection', res.data, { root: true });
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