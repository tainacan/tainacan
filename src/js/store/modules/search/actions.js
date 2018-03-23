import axios from '../../../axios/axios';
import qs from 'qs';

// METAQUERIES ----------------------------------------------------
export const search_by_collection = ({  state, dispatch, commit }, collectionId) => {
    return new Promise((resolve, reject) =>{
        axios.tainacan.get('/collection/' + collectionId + '/items?' + qs.stringify( state.postquery ))
            .then(res => {
                let items = res.data;
                dispatch('collection/setItems', res.data, { root: true } );
                commit('setTotalItems', res.headers['x-wp-total'] );

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

export const setTotalItems = ({ commit }, total ) => {
    commit('setTotalItems', total);
};

export const setPage = ({ commit }, page ) => {
    commit('setPostQueryAttribute', {  attr: 'paged', value: page } );
};

export const setItemsPerPage = ({ commit }, page ) => {
    commit('setPostQueryAttribute', {  attr: 'perpage', value: page } );
};