import Vue from 'vue';

export const setPostQuery = ( state, field, value ) => {
    Vue.set( state.post_query, field, value );
};

export const addMetaQuery = ( state, filter ) => {
    let index = state.meta_query.findIndex( item => item.key === filter.field_id);
    if ( index >= 0){
        Vue.set( state.meta_query, index, {
            key: filter.field_id,
            value: filter.value,
            compare: filter.compare,
            type: filter.type
        } );
    }else{
        state.meta_query.push({
            key: filter.field_id,
            value: filter.value,
            compare: filter.compare,
            type: filter.type
        });
    }
};

export const removeMetaQuery = ( state, filter ) => {
    let index = state.meta_query.findIndex( item => item.key === filter.field_id);
    if (index >= 0) {
        state.meta_query.splice(index, 1);
    }
}
