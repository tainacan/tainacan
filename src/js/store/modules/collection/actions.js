import axios from '../../../axios/axios';

export const fetchItems = ({ commit, state }) => {
    axios.get('/')
        .then(res => {})
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