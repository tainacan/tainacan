import axios from '../../axios/axios';

const state = {
    item: null,
    metadata:[],
    isInit:false,
};

const mutations = {
    setItem( state, item){
        state.item = item;
    },
    setMetadata( state, metadata){
        state.metadata = metadata;
    },
    setSingleMetadata( state, metadata){
        let index = state.metadata.findIndex(itemMetadata => itemMetadata.metadata_id === metadata.metadata_id);
        if ( index >= 0){
            state.metadata[index] = metadata;
        }else{
            state.metadata.push( metadata );
        }
    },
    setInit( state, init){
        state.isInit = init;
    },
};

const actions = {
    sendMetadata: ( { commit }, { item_id, metadata_id, values }) => {
        axios.post('/metadata/item/'+item_id, {
            metadata_id: metadata_id,
            values: values
        })
        .then( res => {
            console.log( res );
            commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
        })
        .catch(error => console.log( error ));
    },
    setSingleMetadata: ({ commit }, { item_id, metadata_id, values }) => {
        console.log(item_id, metadata_id, values)
        commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
    }
};

const getters = {
    getMetadata: state => {
        return state.metadata;
    }
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}