import { tainacan } from '../../../axios/axios.js';

export const fetchAvailableExposers = ({ commit }) => {

    return new Promise((resolve, reject) => {
        tainacan.get('/exposers/' )
            .then(res => {
                commit('setAvailableExposers', res.data);
                resolve(res.data);
            })
            .catch(error => {
                reject(error);
            })
    });
};