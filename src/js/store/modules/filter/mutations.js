import Vue from 'vue';

export const setPostQuery = ( state, { attr, value }) => {
    Vue.set( state.postquery, attr , value );
};

export const addMetaQuery = ( state, filter ) => {
    let index = state.postquery.metaquery.findIndex( item => item.key === filter.field_id);
    if ( index >= 0){
        Vue.set( state.postquery.metaquery, index, {
            key: filter.field_id,
            value: filter.value,
            compare: filter.compare,
            type: filter.type
        } );
    }else{
        state.postquery.metaquery.push({
            key: filter.field_id,
            value: filter.value,
            compare: filter.compare,
            type: filter.type
        });
    }
};

export const removeMetaQuery = ( state, filter ) => {
    let index = state.postquery.metaquery.findIndex( item => item.key === filter.field_id);
    if (index >= 0) {
        state.metaquery.splice(index, 1);
    }
}
