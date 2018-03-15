import axios from '../../../axios/axios';
import qs from 'qs';

// METAQUERIES ----------------------------------------------------
export const search_by_collection = ({  state, dispatch, rootGetters }, collectionId) => {
    return new Promise((resolve, reject) =>{
        axios.tainacan.get('/collection/' + collectionId + '/items?' + qs.stringify( state.postquery ))
            .then(res => {
                let items = res.data;
                dispatch('collection/setItems', res.data, { root: true } );
                dispatch('collection/setTotalItems', res.headers['x-wp-total'], { root: true } );
                resolve({'items': items, 'total': res.headers['x-wp-total'] });
            })
            .catch(error => {
                 reject( error )
            })
    });
};

export const set_postquery_attribute = ({ commit }, filter, value ) => {
    commit('setPostQueryAttribute', {  attr: filter, value: value } );
};

export const set_postquery = ({ commit }, postquery ) => {
    commit('setPostQuery', postquery );
};

export const add_metaquery = ( { commit }, filter  ) => {
    if( filter && filter.value.length === 0 ){
        commit('removeMetaQuery', filter  );
    } else {
        commit('addMetaQuery', filter  );
    }
};

export const remove_metaquery = ( { commit }, filter  ) => {
    commit('removeMetaQuery', filter  );
};

// FILTERS --------------------------------------------------------
export const fetchFilters = ({ commit }, {collectionId, isRepositoryLevel}) => {
    return new Promise((resolve, reject) => {
        let endpoint = '';
        if (!isRepositoryLevel) 
            endpoint = '/collection/' + collectionId + '/filters?context=edit';
        else
            endpoint = '/filters?context=edit';

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
}


export const fetchFilter = ({ commit }, {collectionId, filterId, isRepositoryLevel}) => {
    return new Promise((resolve, reject) => {

        let endpoint = '';
        if (!isRepositoryLevel) 
            endpoint = '/collection/' + collectionId + '/filters/' + filterId; 
        else
            endpoint = '/filters/' + filterId;

        axios.tainacan.get(endpoint + '?context=edit')
        .then((res) => {
            let filter = res.data;
            commit('setSingleFilter', filter);
            resolve (filter);
        }) 
        .catch((error) => {
            console.log(error);
            reject(error);
        });
    });
}

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

export const updateFilter = ( { commit }, { filterId, options }) => {
    return new Promise(( resolve, reject ) => {
        let endpoint = '/filters/' + filterId;

        axios.tainacan.put(endpoint, options)
            .then( res => {
                commit('setSingleFilter', res.data);
                resolve( res.data );
            })
            .catch(error => {
                console.log(error);
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};


export const deleteFilter = ({ commit }, filterId ) => {
    let endpoint = '/filters/' + filterId;

    return new Promise((resolve, reject) => {
        axios.tainacan.delete(endpoint, { data:{ is_permanently: false }})
        .then( res => {
            commit('deleteFilter', filterId );
            resolve( res.data );
        }).catch((error) => { 
            console.log(error);
            reject( error );
        });

    }); 
};

export const updateCollectionFiltersOrder = ({ commit }, { collectionId, filtersOrder }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/collections/' + collectionId, {
            filters_order: filtersOrder
        }).then( res => {
            commit('setCollection', res.data);
            resolve( res.data );
        }).catch( error => { 
            reject( error.response );
        });

    });
}

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
}   
