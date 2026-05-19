import { tainacanApi } from '../../../axios';

/**
 * Dispatches `exposer/fetchAvailableExposers`.
 * @returns {*} Action result.
 */
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