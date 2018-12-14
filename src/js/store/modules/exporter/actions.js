import { tainacan } from '../../../axios/axios.js';

export const fetchAvailableExporters = () => {

    return tainacan.get('/exporters/available')
        .then(response => {
            return response.data;
        })
        .catch(error => {
            console.error(error.response.data);
        })
};

export const createExporterSession = ({commit}, slug) => {

    return tainacan.post('/exporters/session', { exporter_slug: slug })
        .then(response => {
            commit('setExporterSession', response.data);

            return response.data;
        })
        .catch(error => {
            console.error(error.response.data);
        })
};

export const updateExporterSession = ({commit}, exporterSessionUpdated) => {

    return tainacan.patch(`/exporters/session/${exporterSessionUpdated.id}`, exporterSessionUpdated.body)
        .then(response => {
            commit('setExporterSession');

            return response;
        })
        .catch(error => {
            console.error(error.response.data);
        });
};

export const runExporterSession = ({commit}, exporterSessionID) => {

    return tainacan.post(`/exporters/session/${exporterSessionID}/run`)
        .then(response => {
            commit('setBackGroundProcessID', response.data);

            return response.data;
        })
        .catch(error => {
            console.error(error.response.data);
        })
};