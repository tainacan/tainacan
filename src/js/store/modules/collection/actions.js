import axios from '../../../axios/axios';

export const fetchItems = ({ commit, state }, { collectionId, page, itemsPerPage }) => {
    return new Promise ((resolve, reject) => {
        axios.get('/collection/'+collectionId+'/items?paged='+page+'&perpage='+itemsPerPage)
        .then(res => {
            let items = res.data;
            commit('setItems', items);
            resolve({'items': items, 'total': res.headers['x-wp-total'] });
        })
        .catch(error => reject(error));
    });
}

export const deleteItem = ({ commit }, item_id ) => {
    return new Promise((resolve, reject) => {
        axios.delete('/items/' + item_id)
        .then( res => {
            commit('deleteItem', { id: item_id });
            resolve( res );
        }).catch((error) => { 
            reject( error );
        });

    });
};

export const fetchCollections = ({commit} , { page, collectionsPerPage }) => {
    return new Promise((resolve, reject) => {
        axios.get('/collections?paged='+page+'&perpage='+collectionsPerPage)
        .then(res => {
            let collections = res.data;
            commit('setCollections', collections);
            resolve({'collections': collections, 'total': res.headers['x-wp-total'] });
        }) 
        .catch(error => {
            console.log(error);
            reject(error);
        });
    });
}

export const fetchFields = ({ commit }, id) => {
    return new Promise((resolve, reject) => {
        axios.get('/collection/'+id+'/fields')
        .then((res) => {
            let fields= res.data;
            commit('setFields', fields);
            resolve (fields);
        })
        .catch((error) => {
            console.log(error);
            reject(error);
        });
    });
}

export const sendField = ( { commit }, { collectionId, name, fieldType, status }) => {
    return new Promise(( resolve, reject ) => {
        axios.post('/collection/'+ collectionId + '/fields/', {
            name: name,
            field_type: fieldType, 
            status: status
        })
            .then( res => {
                commit('setField', { collection_id: collectionId, name: name, field_type: fieldType, status: status });
                resolve( res.data );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const deleteField = ({ commit }, { collectionId, fieldId }) => {
    return new Promise((resolve, reject) => {
        axios.delete('/collection/' + collectionId + '/fields/' + fieldId)
        .then( res => {
            commit('deleteField', { fieldId } );
            resolve( res.data );
        }).catch((error) => { 
            console.log(error);
            reject( error );
        });

    }); 
};

export const fetchCollection = ({ commit }, id) => {
    return new Promise((resolve, reject) =>{ 
        axios.get('/collections/' + id)
        .then(res => {
            let collection = res.data;
            commit('setCollection', collection);
            resolve( res.data );
        })
        .catch(error => {
            reject(error);
        })
    });
}

export const deleteCollection = ({ commit }, id) => {
    return new Promise((resolve, reject) =>{ 
        axios.delete('/collections/' + id)
        .then(res => {
            let collection = res.data;
            commit('deleteCollection', collection);
            resolve( res.data );
        })
        .catch(error => {
            reject(error);
        })
    });
}

export const updateCollection = ({ commit }, { collection_id, name, description, status }) => {
    return new Promise((resolve, reject) => {
        axios.patch('/collections/' + collection_id, {
            name: name,
            description: description,
            status: status 
        }).then( res => {
            commit('setCollection', { id: collection_id, name: name, description: description, status: status });
            resolve( res.data );
        }).catch( error => { 
            reject( error.response );
        });

    });
}

export const updateCollectionFieldsOrder = ({ commit }, { collectionId, fieldsOrder }) => {
    return new Promise((resolve, reject) => {
        axios.patch('/collections/' + collectionId, {
            fields_order: fieldsOrder
        }).then( res => {
            commit('setCollection', res.data);
            resolve( res.data );
        }).catch( error => { 
            reject( error.response );
        });

    });
}

export const sendCollection = ( { commit }, { name, description, status }) => {
    return new Promise(( resolve, reject ) => {
        axios.post('/collections/', {
            name: name,
            description: description,
            status: status
        })
            .then( res => {
                commit('setCollection', { name: name, description: description, status: status });
                resolve( res.data );
            })
            .catch(error => {
                reject( error.response );
            });
    });
 };


 export const fetchFieldTypes = ({ commit} ) => {
    return new Promise((resolve, reject) => {
        axios.get('/field-types')
        .then((res) => {
            let fieldTypes = res.data;
            commit('setFieldTypes', fieldTypes);
            resolve (fieldTypes);
        })
        .catch((error) => {
            console.log(error);
            reject(error);
        });
    });
}

export const setItems = ({ commit }, items ) => {
    commit('setItems', items);
};