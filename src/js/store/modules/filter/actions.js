import axios from '../../../axios/axios';
import qs from 'qs';

export const search_by_collection = ({ commit, state, getters }, collectionId) => {
    return new Promise((resolve, reject) =>{
        axios.get('/collection/' + collectionId + '/items?' + qs.stringify( state.postquery ))
            .then(res => {
                 resolve( res.data );
            })
            .catch(error => {
                 reject( error )
            })
    });
};


export const set_postquery_attribute = ({ commit }, field, value ) => {
    commit('setPostQuery', {  attr: field, value: value } );
};

export const add_metaquery = ( { commit }, filter  ) => {
    commit('addMetaQuery', filter  );
};

export const remove_metaquery = ( { commit }, filter  ) => {
    commit('removeMetaQuery', filter  );
};



