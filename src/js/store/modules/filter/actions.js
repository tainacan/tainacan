import axios from '../../../axios/axios';
import qs from 'qs';

export const do_query = ({ commit, state }) => {
    return new Promise((resolve, reject) =>{ 
        axios.get('/collections/' + state.collection + '/items?' + qs.stringify( state.query ))
        .then(res => {

        })
        .catch(error => {

        })
    });
}
