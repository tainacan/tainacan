import axios from '../../../axios/axios';

export const fetchItems = ({ commit, state }, collectionId) => {
    axios.get('/collection/'+collectionId+'/items')
        .then(res => {
            let items = res.data;
            commit('setItems', items);
        })
        .catch(error => console.log( error ));
}

export const fetchCollections = ({ commit }) => {
    axios.get('/collections')
        .then(res => {
            let collections = res.data;
            commit('setCollections', collections);
        })
        .catch(error => console.log(error));
}

export const fetchCollection = ({ commit }, id) => {
    return new Promise((resolve, reject) =>{ 
        axios.get('/collections/' + id)
        .then(res => {
            console.log(res);
            let collection = res.data;
            commit('setCollection', collection);
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
