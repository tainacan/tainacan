import { tainacan } from '../../../axios';

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

    return new Promise(( resolve, reject ) => { 
        tainacan.patch(`/exporters/session/${exporterSessionUpdated.id}`, exporterSessionUpdated.body)
            .then(response => {
                commit('setExporterSession');
                resolve( response.data );
            })
            .catch(error => {
                reject( error.response.data );
            });
    });
};

export const runExporterSession = ({commit}, exporterSessionID) => {

    return new Promise(( resolve, reject ) => { 
        tainacan.post(`/exporters/session/${exporterSessionID}/run`)
            .then(response => {
                commit('setBackGroundProcessID', response.data);
                resolve(response.data);
            })
            .catch(error => {
                reject( error.response.data );
            })
        });
};