import { tainacan } from '../../../axios/axios.js';

export const fetchAvailableExporters = ({commit}) => {

    return tainacan.get('/exporters/available')
        .then(response => {
            console.info(response.data);
            return response.data;
        })
        .catch(error => {
            console.error(error.response);
            return error.response.data;
        })
};

export const createExporterSession = ({commit}) => {

    return tainacan.get()
        .then(response => {
            console.info(response.data);
            return response.data;
        })
        .catch(error => {
            console.error(error.response);
            return error.response.data;
        })
};