import { tainacanApi } from '../../../axios';

export const fetchAvailableExposers = ({ commit }) => {

    return new Promise((resolve, reject) => {
        tainacanApi.get('/exposers/' )
            .then(res => {
                commit('setAvailableExposers', res.data);
                resolve(res.data);
            })
            .catch(error => {
                reject(error);
            })
    });
};