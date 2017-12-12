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
    addMetadata( state, metadata){
        state.metadata.push( metadata );
    },
    updateMetadata( state, metadata){
        var index = state.metadata.findIndex(itemMetadata => itemMetadata.metadata_id === metadata.metadata_id);
        state.metadata[index] = metadata;
    },
    setInit( state, init){
        state.isInit = init;
    },
};

const actions = {
    initItem: ( { commit }, item) => {
        commit('setItem', { id: 1, title: 'item' });
    },
    initMetadata: ( { commit }, item) => {
        commit('setMetadata', [
            { item_id: 1, metadata_id: 1, values: [ 'valor' ] }
        ]);
    },
    addMetadata: ( { commit }, { item_id, metadata_id, values }) => {
        // axios.post('/metadata/item/'+item, {
        //     metadata_id: metadata,
        //     values: values
        // })
        // .then( res => {
        //     commit('setMetadata', res);
        // })
        // .catch(error => console.log( error ));
        commit('addMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
    },
    updateMetadata: ( { commit }, { item_id, metadata_id, values }) => {
        // axios.post('/metadata/item/'+item, {
        //     metadata_id: metadata,
        //     values: values
        // })
        // .then( res => {
        //     commit('setMetadata', res);
        // })
        // .catch(error => console.log( error ));
        commit('updateMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
    }
};

const getters = {
    getItem: state => {
        return state.item;
    },
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