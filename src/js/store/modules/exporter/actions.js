import { tainacan } from '../../../axios/axios.js';

export const fetchAvailableExporters = ({commit}) => {

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