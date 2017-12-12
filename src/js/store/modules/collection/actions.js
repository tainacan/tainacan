import axios from '../../../axios/axios';

export const fetchItems = ({ commit, state }) => {
    axios.get('/')
        .then(res => {})
        .catch(error => console.log( error ));
}