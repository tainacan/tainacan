import { tainacanApi } from '../../../axios';

/**
 * Dispatches `exporter/fetchAvailableExporters`.
 * @returns {*} Action result.
 */
export const fetchAvailableExporters = () => {

    return tainacanApi.get('/exporters/available')
        .then(response => {
            return response.data;
        })
        .catch(error => {
            console.error(error.response.data);
        })
};

/**
 * Dispatches `exporter/createExporterSession`.
 * @returns {*} Action result.
 */
export const createExporterSession = ({commit}, slug) => {

    return tainacanApi.post('/exporters/session', { exporter_slug: slug })
        .then(response => {
            commit('setExporterSession', response.data);

            return response.data;
        })
        .catch(error => {
            console.error(error.response.data);
        })
};

/**
 * Dispatches `exporter/updateExporterSession`.
 * @returns {*} Action result.
 */
export const updateExporterSession = ({commit}, exporterSessionUpdated) => {

    return new Promise(( resolve, reject ) => { 
        tainacanApi.put(`/exporters/session/${exporterSessionUpdated.id}`, exporterSessionUpdated.body)
            .then(response => {
                commit('setExporterSession');
                resolve( response.data );
            })
            .catch(error => {
                reject( error.response.data );
            });
    });
};

/**
 * Dispatches `exporter/runExporterSession`.
 * @returns {*} Action result.
 */
export const runExporterSession = ({commit}, exporterSessionID) => {

    return new Promise(( resolve, reject ) => { 
        tainacanApi.post(`/exporters/session/${exporterSessionID}/run`)
            .then(response => {
                commit('setBackGroundProcessID', response.data);
                resolve(response.data);
            })
            .catch(error => {
                reject( error.response.data );
            })
        });
};